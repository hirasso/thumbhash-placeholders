<?php

namespace Hirasso\WPThumbhash\WPCLI\Commands;

use Hirasso\WPThumbhash\Plugin;
use Snicco\Component\BetterWPCLI\Command;
use Snicco\Component\BetterWPCLI\Input\Input;
use Snicco\Component\BetterWPCLI\Output\Output;
use Snicco\Component\BetterWPCLI\Style\SniccoStyle;
use Snicco\Component\BetterWPCLI\Synopsis\InputArgument;
use Snicco\Component\BetterWPCLI\Synopsis\Synopsis;
use WP_Query;
use WP_CLI;

/**
 * A WP CLI command to generate thumbhash placeholders
 * @see https://github.com/snicco/better-wp-cli
 * @TODO see how to organize output helper methods
 */
class ClearCommand extends Command
{
    protected static string $name = 'clear';

    protected static string $short_description = 'Clear thumbhash placeholders';

    protected SniccoStyle $io;

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
        );
    }

    public function execute(Input $input, Output $output): int
    {
        $this->io = new SniccoStyle($input, $output);

        $ids = $input->getRepeatingArgument('ids', []);

        $queryArgs = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => Plugin::META_KEY,
                    'compare' => 'EXISTS',
                ],
            ],
        ];
        if (!empty($ids)) {
            $queryArgs['post__in'] = array_map('absint', array_map('trim', $ids));
        }

        $query = new WP_Query($queryArgs);

        $output->newLine();

        if (!$query->have_posts()) {
            WP_CLI::success('No images with placeholders found.');
            return Command::SUCCESS;
        }
        $count = 0;
        foreach ($query->posts as $id) {
            delete_post_meta($id, Plugin::META_KEY);
            $output->writeln("Removed placeholder for attachment ID: $id");
            $count++;
        }

        $output->newLine();

        WP_CLI::success("$count placeholders cleared");

        return Command::SUCCESS;
    }

    /**
     * Create a status line, for example:
     * image.jpg ..................................................... generated ✔︎
     */
    private function getStatusLine(string $start, string $end): string
    {
        $dots = str_repeat('.', max(0, 70 - strlen($start)));
        return "$start $dots $end";
    }

    /**
     * Make sure all ids are numeric
     */
    private function validateArgumentIds(array $ids): bool
    {
        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                $this->io->error("Invalid non-numeric id provided: $id");
                return false;
            }
        }
        return true;
    }
}
