<?php
/*
 * This file is part of the shochdoerfer GitHub page.
 *
 * (c) Stephan Hochdörfer
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

$maxBlogpostsToRender = 4;
$jsonFeedUrl = 'https://blog.bitexpert.de/blog/feed.json';
$readmeFile = dirname(__FILE__).'/../README.md';

echo "Downloading JSON blog posts feed...\n";
$jsonFeed = file_get_contents($jsonFeedUrl);
$blogposts = json_decode($jsonFeed, true, 512, JSON_THROW_ON_ERROR);

echo "Extracting latest blog posts from feed...\n";
$latestBlogposts = [];
if(isset($blogposts['items']) and is_array($blogposts['items'])) {
    foreach($blogposts['items'] as $post) {
        if(isset($post['author']) && is_array($post['author']) && isset($post['author']['name']) && $post['author']['name'] === 'Stephan Hochdörfer') {
            $latestBlogposts[] = $post;

            if (count($latestBlogposts) > $maxBlogpostsToRender) {
                break;
            }
        }
    }
}

echo "Building Markdown content...\n";
$markdownBlogpostCollection = '';
foreach($latestBlogposts as $post) {
    if(!isset($post['title']) || !isset($post['url'])) {
        continue;
    }

    $markdownBlogpostCollection .= '- ['.$post['title'].']('.$post['url'].')'."\n";
}

echo "Updating README.md file...\n";
if(!empty($markdownBlogpostCollection)) {
    if(!file_exists($readmeFile)) {
        echo "README.md file not found!\n";
        exit -1;
    }

    $markdownBlogpostCollection = "<!--- blog_start --->\n".$markdownBlogpostCollection."<!--- blog_end --->";
    $readmeFileContents = file_get_contents($readmeFile);
    $readmeFileContents = preg_replace('#<!--- blog_start --->([^{]+)<!--- blog_end --->#m', $markdownBlogpostCollection, $readmeFileContents);
    file_put_contents($readmeFile, $readmeFileContents);
}
