<?php

/**
 * Product Videos Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SellMediaVideos extends SellMediaProducts {

    function __construct(){
        add_filter( 'sell_media_quick_view_post_thumbnail', array( $this, 'quick_view_thumbnail' ), 10, 2 );
    }

    /**
     * Replace image with video.
     * @param  string $html    Post thumbnail.
     * @param  int $post_id Id of the post.
     * @return string          Updated video or image.
     */
    function quick_view_thumbnail( $html, $post_id ){
        $first_video =  $this->get_first_embed_media( $post_id );
        if( $first_video ){
            return $first_video;
        }

        return $html;
    }

    /**
     * Get first video from the post content.
     * @param  int $post_id Id of the post.
     * @return mixed          First video embed code or false.
     */
    function get_first_embed_media( $post_id ) {

        $post = get_post( $post_id );
        if ( $post && $post->post_content ) {
            $content = do_shortcode( apply_filters( 'the_content', $post->post_content ) );
            $videos =  get_media_embedded_in_content( $content ) ;
            if( !empty( $videos ) ){
                return $videos[0];
            }
        }

        return false;
    }

}

new SellMediaVideos();