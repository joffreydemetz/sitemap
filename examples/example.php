<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

try {
    $publicPath = realpath(__DIR__ . '/public');

    $index = new \JDZ\Sitemap\Index($publicPath);

    $sitemap = new \JDZ\Sitemap\Map($publicPath, 'sitemap', 'https://domain.tld');
    $sitemap->addItem(new \JDZ\Sitemap\Url('/', 'now', \JDZ\Sitemap\Url::DAILY, 0.9));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/test/'));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/test2/'));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/test3/', 'now', \JDZ\Sitemap\Url::MONTHLY, 0.2));
    $sitemap->write();

    foreach ($sitemap->writtenFilePaths as $path) {
        $index->addItem(new \JDZ\Sitemap\Group('https://domain.tld/sitemap/' . $path));
    }

    $sitemap = new \JDZ\Sitemap\Map($publicPath, 'subdomain', 'https://sub.domain.tld');
    $sitemap->addItem(new \JDZ\Sitemap\Url('/', 'now', \JDZ\Sitemap\Url::DAILY, 0.9));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/testsub/'));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/testsub2/'));
    $sitemap->addItem(new \JDZ\Sitemap\Url('/testsub3/', 'now', \JDZ\Sitemap\Url::MONTHLY, 0.2));
    $sitemap->write();

    foreach ($sitemap->writtenFilePaths as $path) {
        $index->addItem(new \JDZ\Sitemap\Group('https://domain.tld/sitemap/' . $path));
    }

    $index->write();

    echo 'Everything went well !';
} catch (\Throwable $e) {
    echo $e->getMessage();
}
exit();
