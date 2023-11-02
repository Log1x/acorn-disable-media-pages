<?php

namespace Log1x\DisableMediaPages;

use Illuminate\Support\Str;

class DisableMediaPages
{
    /**
     * Initialize the DisableMediaPages instance.
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('wp_unique_post_slug', [$this, 'randomizeAttachmentSlug'], 10, 4);
        add_filter('attachment_link', [$this, 'changeAttachmentPageLink'], 10, 2);
        add_filter('template_redirect', [$this, 'disableAttachmentPages']);
        add_filter('redirect_canonical', [$this, 'disableCanonicalAttachmentPages'], 10, 2);
    }

    /**
     * Randomize attachment slugs.
     *
     * @param  string  $slug
     * @param  int  $post_id
     * @param  string  $post_status
     * @param  string  $post_type
     * @return string
     */
    public function randomizeAttachmentSlug($slug, $post_id, $post_status, $post_type)
    {
        if ($post_type !== 'attachment' || Str::isUuid($slug)) {
            return $slug;
        }

        return (string) Str::uuid();
    }

    /**
     * Change the attachment page link to the attachment URL.
     *
     * @param  string  $url
     * @param  int  $id
     * @return string
     */
    public function changeAttachmentPageLink($url, $id)
    {
        return wp_get_attachment_url($id) ?? $url;
    }

    /**
     * 404 requests to attachment pages.
     *
     * @return void
     */
    public function disableAttachmentPages()
    {
        if (! is_attachment()) {
            return;
        }

        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }

    /**
     * 404 requests to canonical attachment pages.
     *
     * @param  string  $redirect_url
     * @param  string  $requested_url
     * @return void
     */
    public function disableCanonicalAttachmentPages($redirect_url, $requested_url)
    {
        if (! is_attachment()) {
            return $redirect_url;
        }

        global $wp_query;

        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}
