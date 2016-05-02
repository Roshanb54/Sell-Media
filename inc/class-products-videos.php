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

        add_filter( 'sell_media_grid_item_class', array( $this, 'add_class' ), 10, 2 );

        add_action( 'sell_media_after_options_meta_box', array( $this, 'add_meta_fields' ), 11 );

        add_action( 'sell_media_extra_meta_save', array( $this, 'save_meta_fields' ) );
        add_action( 'wp_ajax_check_attachment_is_audio_video', array( $this, 'check_attachment_is_audio_video' ) );
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
        if( !$this->is_video_item( $post_id ) )
            return false;

        $url = get_post_meta( $post_id, 'sell_media_embed_link', true );
        if( '' != $url ){
            return wp_oembed_get( esc_url( $url ) );
        }

        return false;
    }

    /**
     * Add meta fields.
     * @param int $post_id ID of post.
     */
    function add_meta_fields( $post_id ){
        $embed_url = get_post_meta( $post_id, 'sell_media_embed_link', true );
        ?>
        <div id="sell-media-embed-link-field" class="sell-media-field" style="display:none;">
            <label for="sell-media-embed-link"><?php _e( 'Preview URL', 'sell_media' ); ?></label>
            <input name="sell_media_embed_link" id="sell-media-embed-link" class="" type="text" placeholder="" value="<?php echo esc_url( $embed_url ); ?>" />
        </div>
        <?php
    }

    /**
     * Save meta fields.
     * @param  int $post_id ID of post.
     * @return void          
     */
    function save_meta_fields( $post_id ){

        if( isset( $_POST['sell_media_embed_link'] ) ){
            update_post_meta( $post_id, 'sell_media_embed_link', esc_url_raw( $_POST['sell_media_embed_link'] ) );
        } 
    }

    /**
     * Check if attachment is audio or video.
     */
    function check_attachment_is_audio_video(){
        if( !is_admin() ){
            echo 'false';
            exit;
        }

        $attachment_id = absint( $_POST['attachment_id'] );

        $is_audio = $this->is_attachment_audio( $attachment_id );
        $is_video = $this->is_attachment_video( $attachment_id );

        if( $is_video || $is_audio ){
            echo 'true';
            exit;
        }

        echo "false";
        exit;
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
     * Check if item is video type or not.
     * @param  int  $post_id ID of post.
     * @return boolean          True if type is video.
     */
    function is_video_item( $post_id ){
        $attachment_ids = sell_media_get_attachments ( $post_id );
        if( !empty( $attachment_ids ) ){
            foreach ($attachment_ids as $key => $attachment_id) {
                $type = get_post_mime_type($attachment_id);
                switch ($type) {
                    case 'video/x-ms-asf' :
                    case 'video/x-ms-wmv' :
                    case 'video/x-ms-wmx' :
                    case 'video/x-ms-wm' :
                    case 'video/avi' :
                    case 'video/divx' :
                    case 'video/x-flv' :
                    case 'video/quicktime' :
                    case 'video/mpeg' :
                    case 'video/mp4' :
                    case 'video/ogg' :
                    case 'video/webm' :
                    case 'video/x-matroska' :
                      return true; break;
                    default:
                      return false;
                }
            }
        }
    }

    /**
     * Check if attachment is video.
     * @param  int  $attachment_id ID of attachment.
     * @return boolean                True if is video.
     */
    function is_attachment_video( $attachment_id ){
        $type = get_post_mime_type($attachment_id);
        switch ($type) {
            case 'video/x-ms-asf' :
            case 'video/x-ms-wmv' :
            case 'video/x-ms-wmx' :
            case 'video/x-ms-wm' :
            case 'video/avi' :
            case 'video/divx' :
            case 'video/x-flv' :
            case 'video/quicktime' :
            case 'video/mpeg' :
            case 'video/mp4' :
            case 'video/ogg' :
            case 'video/webm' :
            case 'video/x-matroska' :
              return true; break;
            default:
              return false;
        }
    }

    /**
     * Check if attachment is audio.
     * @param  int  $attachment_id ID of attachment.
     * @return boolean                True if is audio.
     */
    function is_attachment_audio( $attachment_id ){
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

    /**
     * Add video class.
     * @param string $classes Class for the item.
     */
    function add_class( $classes, $post_id ){
        if( is_null( $post_id ) ){
            return $classes;
        }
        
        if( $this->is_video_item( $post_id ) ){
            return $classes . ' sell-media-grid-single-video-item';
        }

        return $classes;
    }

}

new SellMediaVideos();