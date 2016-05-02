<?php

/**
 * Product Audio Class
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class SellMediaAudio extends SellMediaProducts {

    function __construct(){
        add_filter( 'sell_media_quick_view_post_thumbnail', array( $this, 'quick_view_thumbnail' ), 10, 2 );

        add_filter( 'sell_media_grid_item_class', array( $this, 'add_class' ), 10, 2 );
    }

    /**
     * Replace image with video.
     * @param  string $html    Post thumbnail.
     * @param  int $post_id Id of the post.
     * @return string          Updated video or image.
     */
    function quick_view_thumbnail( $html, $post_id ){
        $preview_url =  $this->get_preview( $post_id );
        if( $preview_url ){
            return $preview_url;
        }

        return $html;
    }

    /**
     * Get Video/ audio preview.
     * @param  int $post_id ID of post.
     * @return string          Embed video/ audio.
     */
    function get_preview( $post_id ){
        if( !$this->is_audio_item( $post_id ) )
            return false;

        $url = get_post_meta( $post_id, 'sell_media_embed_link', true );
        if( '' != $url ){
            return wp_oembed_get( esc_url( $url ) );
        }

        return false;
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
    
    /**
     * Check if item is audio type or not.
     * @param  int  $post_id ID of post.
     * @return boolean          True if type is audio.
     */
    function is_audio_item( $post_id ){
        $attachment_ids = sell_media_get_attachments ( $post_id );
        if( !empty( $attachment_ids ) ){
            foreach ($attachment_ids as $key => $attachment_id) {
                $type = get_post_mime_type($attachment_id);
                switch ($type) {
                    case 'audio/mpeg' :
                    case 'audio/x-realaudio' :
                    case 'audio/wav' :
                    case 'audio/ogg' :
                    case 'audio/midi' :
                    case 'audio/x-ms-wma' :
                    case 'audio/x-ms-wax' :
                    case 'audio/x-matroska' :
                      return true; break;
                    default:
                      return false;
                }
            }
        }
    }

    /**
     * Add audio class.
     * @param string $classes Class for the item.
     */
    function add_class( $classes, $post_id ){
        if( is_null( $post_id ) ){
            return $classes;
        }

        if( $this->is_audio_item( $post_id ) ){
            return $classes . ' sell-media-grid-single-audio-item';
        }

        return $classes;
    }

}

new SellMediaAudio();