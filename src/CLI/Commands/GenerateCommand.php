<?php

namespace Hirasso\WP\Placeholders\CLI\Commands;

use Hirasso\WP\Placeholders\ImageDownloader;
use Hirasso\WP\Placeholders\Plugin;
use Hirasso\WP\Placeholders\CLI\InputValidator;
use Hirasso\WP\Placeholders\CLI\Utils;
use Snicco\Component\BetterWPCLI\Command;
use Snicco\Component\BetterWPCLI\Input\Input;
use Snicco\Component\BetterWPCLI\Output\Output;
use Snicco\Component\BetterWPCLI\Style\SniccoStyle;
use Snicco\Component\BetterWPCLI\Style\Text;
use Snicco\Component\BetterWPCLI\Synopsis\InputArgument;
use Snicco\Component\BetterWPCLI\Synopsis\InputFlag;
use Snicco\Component\BetterWPCLI\Synopsis\Synopsis;
use WP_Query;

/**
 * @see https://github.com/snicco/better-wp-cli
 */
class GenerateCommand extends Command
{
    protected static string $name = 'generate';

    protected static string $short_description = 'Generate placeholders';

    /**
     * Command synopsis.
     */
    public static function synopsis(): Synopsis
    {
        return new Synopsis(
            new InputArgument(
                'ids',
                'Only generate placeholders for these images',
                InputArgument::OPTIONAL | InputArgument::REPEATING
            ),
            new InputFlag(
                'force',
                'Generate placeholders also for images that already have one'
            ),
        );
    }

    /**
     * Execute the command
     */
    public function execute(Input $input, Output $output): int
    {
        $io = new SniccoStyle($input, $output);

        $ids = $input->getRepeatingArgument('ids', []);
        $force = $input->getFlag('force');

        $io->title("Generating ThumbHash Placeholders");

        $validator = new InputValidator($io);
        if (!$validator->isNumericArray($ids, "Non-numeric ids provided")) {
            return Command::INVALID;
        }

        $queryArgs = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'post__in' => $ids,
            'fields' => 'ids',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- this only ever runs in WP CLI
            'meta_query' => [
                [
                    'key' => Plugin::META_KEY,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        if ($force) {
            unset($queryArgs['meta_query']);
        }

        $query = new WP_Query($queryArgs);

        ImageDownloader::cleanupOldImages();

        if (!$query->have_posts()) {
            $io->success("No images without placeholders found");
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($query->posts as $id) {
            $thumbhash = Plugin::generateThumbhash($id);
            $status = match (!!$thumbhash) {
                true => $io->colorize('generated ✔︎', Text::GREEN),
                default => $io->colorize('failed ❌', Text::RED)
            };
            $output->writeln(Utils::getStatusLine(basename(wp_get_attachment_url($id)), $status));
            if ($thumbhash) {
                $count++;
            }
        }
        $output->newLine();

        $io->success(match ($count) {
            1 => "$count placeholder generated",
            0 => "No placeholders generated",
            default => "$count placeholders generated"
        });

        return Command::SUCCESS;
    }
}
