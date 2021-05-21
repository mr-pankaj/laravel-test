<?php


namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Routing\Controller as BaseController;

class MenuController extends BaseController
{
    /*
    Requirements:
    - the eloquent expressions should result in EXACTLY one SQL query no matter the nesting level or the amount of menu items.
    - it should work for infinite level of depth (children of childrens children of childrens children, ...)
    - verify your solution with `php artisan test`
    - do a `git commit && git push` after you are done or when the time limit is over

    Hints:
    - open the `app/Http/Controllers/MenuController` file
    - eager loading cannot load deeply nested relationships
    - a recursive function in php is needed to structure the query results
    - partial or not working answers also get graded so make sure you commit what you have


    Sample response on GET /menu:
    ```json
    [
        {
            "id": 1,
            "name": "All events",
            "url": "/events",
            "parent_id": null,
            "created_at": "2021-04-27T15:35:15.000000Z",
            "updated_at": "2021-04-27T15:35:15.000000Z",
            "children": [
                {
                    "id": 2,
                    "name": "Laracon",
                    "url": "/events/laracon",
                    "parent_id": 1,
                    "created_at": "2021-04-27T15:35:15.000000Z",
                    "updated_at": "2021-04-27T15:35:15.000000Z",
                    "children": [
                        {
                            "id": 3,
                            "name": "Illuminate your knowledge of the laravel code base",
                            "url": "/events/laracon/workshops/illuminate",
                            "parent_id": 2,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        },
                        {
                            "id": 4,
                            "name": "The new Eloquent - load more with less",
                            "url": "/events/laracon/workshops/eloquent",
                            "parent_id": 2,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        }
                    ]
                },
                {
                    "id": 5,
                    "name": "Reactcon",
                    "url": "/events/reactcon",
                    "parent_id": 1,
                    "created_at": "2021-04-27T15:35:15.000000Z",
                    "updated_at": "2021-04-27T15:35:15.000000Z",
                    "children": [
                        {
                            "id": 6,
                            "name": "#NoClass pure functional programming",
                            "url": "/events/reactcon/workshops/noclass",
                            "parent_id": 5,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        },
                        {
                            "id": 7,
                            "name": "Navigating the function jungle",
                            "url": "/events/reactcon/workshops/jungle",
                            "parent_id": 5,
                            "created_at": "2021-04-27T15:35:15.000000Z",
                            "updated_at": "2021-04-27T15:35:15.000000Z",
                            "children": []
                        }
                    ]
                }
            ]
        }
    ]
     */

    public function getMenuItems()
    {
        $menuItems = MenuItem::orderBy('parent_id', 'asc')->get();

        //https://gist.github.com/ArneGockeln/d2b210456770d306407ff3b6fe9a8cbc
        // This function searches for needle inside of multidimensional array haystack
        // Returns the path to the found element or false
        function in_array_multi($needle, $haystack)
        {
            if (!is_array($haystack)) return false;

            foreach ($haystack as $key => $value) {
                if ($value == $needle) {
                    return $key;
                } else if (is_array($value)) {
                    // multi search
                    $key_result = in_array_multi($needle, $value);
                    if ($key_result !== false) {

                        return $key . '_' . $key_result;
                    }
                }
            }

            return false;
        }

        $menuTree = [];

        foreach ($menuItems as $menu) {

            if (!$menu['parent_id']) {
                $menuTree[] = $menu->toArray();
            } else {
                $key_path = in_array_multi($menu['parent_id'], $menuTree);

                if ($key_path !== false) {
                    $path = explode('_', $key_path);
                    $result = [];

                    foreach ($path as $key => $xpath) {
                        if ($key == 0) {
                            $result = &$menuTree[$xpath];
                        } else {
                            if (is_array($result[$xpath])) {
                                $result = &$result[$xpath]; // Return reference so that it can be later
                            }
                        }
                    }

                    if (isset($result['children'])) {
                        $result['children'][] = $menu->toArray();
                    } else {
                        $result['children'] = [];
                        $result['children'][] = $menu->toArray();
                    }

                    unset($result); // Reset the result so that it can't create the last reference issue
                }
            }
        }

        return response()->json($menuTree);
    }
}
