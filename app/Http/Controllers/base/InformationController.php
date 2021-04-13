<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use MarkConverter;

class InformationController extends Controller {

    public function readme () {
        $helper = new InfoMethods();
        $parsed = $helper->provideFile('README.md');

        return view('base.markdown_file', [
            'header' => 'readme',
            'content' => $parsed['html'],
            'date' => $parsed['date']
        ]);
    }

    public function license () {
        $helper = new InfoMethods();
        $parsed = $helper->provideFile('LICENSE.md');

        return view('base.markdown_file', [
            'header' => 'license',
            'content' => $parsed['html'],
            'date' => $parsed['date']
        ]);
    }

    public function licensing () {
        $helper = new InfoMethods();
        $parsed = $helper->provideFile('LICENSE.md');

        return view('base.markdown_file', [
            'header' => 'license',
            'content' => $parsed['html'],
            'date' => $parsed['date']
        ]);
    }

    public function wikiIndex () {
        $helper = new InfoMethods();
        $parsed = $helper->provideFile('DOCUMENTATION.md');
        $content = $parsed['html'];
        $toc = [];

        $content = preg_replace_callback(
            '|<h[^>]+>(.*)</h[^>]+>|iU',
            function ($h) use (&$toc) {
                $i = intval(substr($h[0], 2, 1));

                if ($i === 1) {
                    return '';
                }
                else {
                    $text = trim($h[1]);
                    $split = explode('(#', $text);
                    $anchor = substr($split[1], 0, -1);
                    $anchor = preg_replace('/[^ \w-]/', '', $anchor);
                    $text = $split[0];

                    $heading = '<h'.$i.' id="'.$anchor.'" class="wiki-h'.$i.'">'.$text.'</h'.$i.'>';
                    $link = '<a href="#'.$anchor.'" class="wiki-l'.$i.'">'.$text.'</a><br/>';

                    $toc[] = $link;
                    return $heading;
                }
            },
            $content
        );

        return view('base.wiki', [
            'toc' => $toc,
            'content' => trim($content),
            'date' => $parsed['date']
        ]);
    }
}


class InfoMethods {

    public function provideFile ($file) {
        $full = base_path().'/'.$file;

        $markdown = file_get_contents($full);
        $date = date("Y-m-d", filemtime($full));

        $converter = new MarkConverter();
        $html = $converter->convertToHtml($markdown);

        return [
            'html' => $html,
            'date' => $date
        ];
    }
}
