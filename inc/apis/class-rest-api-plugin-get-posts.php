<?php
class RestApiPlugin_GetPosts
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'rest_api_plugin_posts_endpoints']);
    }

    /**
     * Register posts endpoints.
     */
    public function rest_api_plugin_posts_endpoints()
    {

        register_rest_route(
            'rest-api-plugin/v1',
            'posts',
            [
                'method'   => 'GET',
                'callback' => [$this, 'rest_api_plugin_endpoint_handler'],
            ]
        );
    }

    public function rest_api_plugin_endpoint_handler(WP_REST_Request $request)
    {
        $response      = [];
        // Error Handling.
        $error = new WP_Error();

        $posts_data = $this->get_posts();

        // If posts found.
        if (!empty($posts_data['posts_data'])) {

            $response['status']      = 200;
            $response['posts_data']  = $posts_data['posts_data'];
            $response['found_posts'] = $posts_data['found_posts'];
        } else {

            // If the posts not found.
            $error->add(404, __('Posts not found', 'rest_api_plugin_posts_endpoints'));

            return $error;
        }

        return new WP_REST_Response($response);
    }

    public function get_posts()
    {

        $args = [
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         =>  5,
            'fields'                 => 'ids',
            'orderby'                => 'date',
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,

        ];

        $post_ids = new WP_Query($args);

        $post_result = $this->get_required_posts_data($post_ids->posts);
        $found_posts = $post_ids->found_posts;

        return [
            'posts_data'  => $post_result,
            'found_posts' => $found_posts,
        ];
    }

    public function get_required_posts_data($post_IDs)
    {

        $post_result = [];

        if (empty($post_IDs) && !is_array($post_IDs)) {
            return $post_result;
        }

        foreach ($post_IDs as $post_ID) {

            $post_data                     = [];
            $post_data['id']               = $post_ID;
            $post_data['title']            = get_the_title($post_ID);
            $post_data['excerpt']          = get_the_excerpt($post_ID);
            $post_data['date']             = get_the_date('', $post_ID);

            array_push($post_result, $post_data);
        }

        return $post_result;
    }
}
new RestApiPlugin_GetPosts();
