<?php

namespace Log1x\DisableMediaPages\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MediaGenerateSlugsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-slugs
                            {--revert : Revert UUID slugs back to original slugs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert media attachment slugs to UUID\'s.';

    /**
     * The package instance.
     *
     * @var \Log1x\DisableMediaPages\DisableMediaPages
     */
    protected $package;

    /**
     * The updated count.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * The UUID pattern.
     *
     * @var string
     */
    protected $pattern = "'^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$'";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->package = app('Log1x/DisableMediaPages');

        if ($this->option('revert')) {
            $this->handleRevert();

            return;
        }

        $this->handleUpdate();
    }

    /**
     * Handle the UUID slug update.
     *
     * @return void
     */
    protected function handleUpdate()
    {
        $attachments = $this->getAttachments();

        if ($attachments->isEmpty()) {
            $this->components->info('All media attachments already have UUID slugs.');

            return;
        }

        if (! $this->components->confirm("Are you sure you want to update <fg=blue>{$attachments->count()}</> media attachments?")) {
            return;
        }

        $timer = microtime(true);

        $attachments->each(function ($attachment) {
            $filename = basename($attachment['url']);

            if (Str::isUuid($attachment['name'])) {
                $this->components->warn("<fg=yellow>{$filename}</> <fg=gray>({$attachment['name']})</> is already a <fg=yellow>UUID</>.");

                return;
            }

            $uuid = (string) Str::uuid();

            wp_update_post([
                'ID' => $attachment['id'],
                'post_name' => $uuid,
            ]);

            $this->components->info("<fg=blue>{$filename}</> <fg=gray>({$attachment['name']})</> has been updated to <fg=blue>{$uuid}</>.");

            $this->count++;
        });

        $timer = round(microtime(true) - $timer, 2);

        $this->newLine();
        $this->components->info("Successfully updated <fg=blue>{$this->count}</> media attachments in <fg=blue>{$timer}</> second(s).");
    }

    /**
     * Handle the UUID slug revert.
     *
     * @return void
     */
    protected function handleRevert()
    {
        $attachments = $this->getAttachments(true);

        if ($attachments->isEmpty()) {
            $this->components->info('All media attachments already have original slugs.');

            return;
        }

        if (! $this->components->confirm("Are you sure you want to revert <fg=blue>{$attachments->count()}</> media attachments?")) {
            return;
        }

        $timer = microtime(true);

        $attachments->each(function ($attachment) {
            $filename = basename($attachment['url']);

            if (! Str::isUuid($attachment['name'])) {
                $this->components->warn("<fg=yellow>{$filename}</> <fg=gray>({$attachment['name']})</> is not a <fg=yellow>UUID</>.");

                return;
            }

            remove_filter('wp_unique_post_slug', [$this->package, 'randomizeAttachmentSlug']);

            $slug = $attachment['title'] ?: Str::beforeLast($filename, '.');

            wp_update_post([
                'ID' => $attachment['id'],
                'post_name' => sanitize_title($slug),
            ]);

            $slug = get_post($attachment['id'])->post_name;

            $this->components->info("<fg=blue>{$filename}</> <fg=gray>({$attachment['name']})</> has been reverted to <fg=blue>{$slug}</>.");

            $this->count++;
        });

        $timer = round(microtime(true) - $timer, 2);

        $this->newLine();
        $this->components->info("Successfully reverted <fg=blue>{$this->count}</> media attachments in <fg=blue>{$timer}</> second(s).");
    }

    /**
     * Get existing attachments that are not UUID's.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAttachments(bool $uuids = false)
    {
        global $wpdb;

        $uuids = $uuids ? 'RLIKE' : 'NOT RLIKE';

        $attachments = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'attachment' AND post_name {$uuids} {$this->pattern}"
        );

        return collect($attachments)->map(fn ($attachment) => [
            'id' => $attachment->ID,
            'name' => $attachment->post_name,
            'title' => $attachment->post_title,
            'url' => $attachment->guid,
        ]);
    }
}
