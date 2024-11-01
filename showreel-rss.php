<?php
/*
Plugin Name: WP MU Showreel RSS
Description: Show a custom RSS feed with the latest posts from all blogs on your website. You can only use this plugin if you have a MU installation. The plugin settings can be found under Options-page.
Author: Norran
Version: 1.3
Author URI: http://norran.se
Plugin URI: http://dev.norran.se/wp/
*/

class MUShowreelRSS {

    private $dir_path;
    private $active;
    private $images;
    private $descriptions;

    const OPT_NAME_ACTIVE_BLOGS = 'MUShoowreelRSS_active_blogs';
    const OPT_NAME_IMAGES = 'MUShoowreelRSS_blog_images';
    const OPT_NAME_DESCRIPTIONS = 'MUShoowreelRSS_blog_descriptions';
    const CACHE_ID = 'MUShoowreelRSS_feed_cache';
    const CACHE_GROUP = 'MUShoowreelRSS_feed';
    const CACHE_EXPIRE_TIME = 3600;

    public function __construct() {
        $this->dir_path = dirname(__FILE__).'/';

        $blog_ids = get_option(self::OPT_NAME_ACTIVE_BLOGS);
        $this->active = is_array($blog_ids) ? $blog_ids : array();

        $img = get_option(self::OPT_NAME_IMAGES);
        $this->images = is_array($img) ? $img : array();

        $descriptions = get_option(self::OPT_NAME_DESCRIPTIONS);
        $this->descriptions = is_array($descriptions) ? $descriptions : array();
    }

    public static function menuItem() {
        $obj = new self();
        add_options_page(
                'Showreel RSS',
                'Showreel RSS',
                'manage_options',
                __FILE__,
                array($obj, 'page')
        );
    }

    public function page() {

        $updated = false;

        // save new list and images
        if(count($_POST) > 0) {

            /*
             * Update activity list
             */
            $this->active = array();
            if(isset($_POST['blogs'])) {
                foreach($_POST['blogs'] as $blog_id)
                    $this->active[] = $blog_id;
            }
            update_option(self::OPT_NAME_ACTIVE_BLOGS, $this->active);

            
            /*
             * Update descriptions
             */
            $this->descriptions = array();
            foreach($_POST['description'] as $blog_id=>$desc)
                $this->descriptions[$blog_id] = $desc;
            update_option(self::OPT_NAME_DESCRIPTIONS, $this->descriptions);
            

            /*
             * Update images
             */
            foreach($_FILES as $blog_id => $file ) {

                if($file['name'] == '')
                    continue;

                // Remove old image
                if($this->getBlogImageUrl($blog_id) != null)
                    unlink($this->getBlogImagePath($blog_id));

                // upload new image
                $f = wp_handle_upload($file);
if(isset($f['error']))
 throw new Exception('img error: '.$f['error']);
                if(isset($f['url'])) {
                    $this->images[$blog_id] = $f;
                }
            }
            update_option(self::OPT_NAME_IMAGES, $this->images);

            // clear cache
            wp_cache_delete(self::CACHE_ID, self::CACHE_GROUP);

            // Boolean variable to be used in template
            $updated = true;
        }

        // Show template
        include $this->dir_path.'/admin_template.php';
    }

    /**
     * Get a list with all blogs as BlogShowreelPresentation- objects
     * @return array <BlogShowreelPresentation>
     */
    public function getBlogs() {

        global $wpdb;
        include_once $this->dir_path . 'BlogShowreelPresentation.php';
        
        $blogs = array();
        $blog_data = $wpdb->get_results($wpdb->prepare("SELECT blog_id, path FROM $wpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' ORDER BY registered DESC", $wpdb->siteid), ARRAY_A );

        foreach($blog_data as $b) {
            $is_active = in_array($b['blog_id'], $this->activeBlogs());
            $name = $this->getBlogDescription($b['blog_id']) == null ?  $b['path'] : $this->getBlogDescription($b['blog_id']);
            $blogs[] = new BlogShowreelPresentation(
                                    $b['blog_id'],
                                    $name,
                                    'http://'.$b['domain'].$b['path'],
                                    $is_active,
                                    $this->getBlogImageUrl($b['blog_id'])
                               );
        }

        return $blogs;
    }

    public function lastCacheTime() {
        $cached_data = wp_cache_get(self::CACHE_ID, self::CACHE_GROUP);
        if($cached_data !== false)
            return $cached_data[0];
        else
            return 0;
    }

    public function activeBlogs() {
        return $this->active;
    }

    public function UrlToRSS() {
        return bloginfo('url').'/'.PLUGINDIR.'/'.basename($this->dir_path).'/rss.php';
    }

    public function getBlogImageUrl($id) {
        return isset($this->images[$id]) ? $this->images[$id]['url'] : null;
    }

    public function getBlogImagePath($id) {
        return isset($this->images[$id]) ? $this->images[$id]['file'] : null;
    }

    public function getBlogDescription($id) {
        return isset($this->descriptions[$id]) ? $this->descriptions[$id] : null;
    }

    public function feed() {

        // Load from cache if exists
        $data = wp_cache_get(self::CACHE_ID, self::CACHE_GROUP);
        if($data !== false)
            return $data[1];


        global $wpdb;
        $data = array();

        foreach($this->activeBlogs() as $id) {
            $feed_data = $wpdb->get_results("SELECT post_date, post_content, guid, post_title FROM wp_".$id."_posts WHERE post_type='post' AND post_status = 'publish' ORDER BY ID DESC LIMIT 0,1", ARRAY_A);
            
            if(count($feed_data) > 0) {
                $img_data = array();
                if($this->getBlogImagePath($id) != null) {
                    $img_data['image_url'] = $this->getBlogImageUrl($id);
                    $img_data['image_size'] = filesize($this->getBlogImagePath($id));
                    $img_info = getimagesize($this->getBlogImagePath($id));
                    $img_data['image_mimetype'] = $img_info['mime'];
                }

                $feed_data[0]['description'] = $this->getBlogDescription($id);
                $data[strtotime($feed_data[0]['post_date'])] = array_merge($feed_data[0], $img_data);
            }
        }

        rsort($data);

        // save to cache
        wp_cache_set(self::CACHE_ID, array(time(), $data), self::CACHE_GROUP, self::CACHE_EXPIRE_TIME);
        
        return $data;
    }
}

add_action('admin_menu', array('MUShowreelRSS', 'menuItem'));

?>