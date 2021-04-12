<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use MarkConverter;

class InformationController extends Controller {

    public function readme () {
        $helper = new InfoMethods();
        $content = $helper->provideFile('README.md');

        return view('base.markdown_file', [
            'header' => 'readme',
            'content' => $content
        ]);
    }

    public function license () {
        $helper = new InfoMethods();
        $content = $helper->provideFile('LICENSE.md');

        return view('base.markdown_file', [
            'header' => 'license',
            'content' => $content
        ]);
    }

    public function licensing () {
        $helper = new InfoMethods();
        $content = $helper->provideFile('LICENSE.md');

        return view('base.markdown_file', [
            'header' => 'license',
            'content' => $content
        ]);
    }

    public function wikiIndex () {
        $helper = new InfoMethods();
        $content = $helper->provideFile('DOCUMENTATION.md');
        $toc = [];

        // split by h2
        $h2 = explode('<h2>', $content);
        unset($h2[0]); // remove H1

        foreach ($h2 as $h2_i => $h2_v) {
            $h2_p = $helper->parseHeading(2, $h2_v);
            $toc[$h2_p['link']] = [];

            $h3 = explode('<h2>', $h2_p['content']);
            foreach ($h3 as $h3_i => $h3_v) {
                $h3_p = $helper->parseHeading(3, $h3_v);
                die(print_r($h3_p ));
                $toc[$h2_p['link']][$h3_p['link']] = [];

                $h3[$h3_i] = $h3_p['heading']."\n".$h3_p['content'];
            }
            $h3 = implode("\n", $h3);

            $h2[$h2_i] = $h2_p['heading']."\n".$h3;
        }


        // Extract Anchors create table of content
        /*foreach ($lines as $i => $v) {
            if (substr($v, 0, 2) === '<h')

            if (substr($v, 0, 2) === '<h') {
                $split = explode('(#', $v);

                if (!empty($split[1])) {
                    $anchor = trim($split[1]);
                    $anchor = substr($anchor, 0, -6);
                    $anchor = preg_replace('/[^ \w-]/', '', $anchor);


                    $v = substr($v, 0, 3).' id="'.$anchor.'"'.substr($split[0], 3).'</'.substr($v, 1, 3);
                }

                $lines[$i] = $v;
            }
        }*/

        $content = implode("\n", $h2);

        return view('base.wiki', [
            'toc' => $toc,
            'content' => $content
        ]);
    }
}


class InfoMethods {

    public function provideFile ($file) {
        $markdown = file_get_contents(base_path().'/'.$file);

        $converter = new MarkConverter();
        $html = $converter->convertToHtml($markdown);

        return $html;
    }

    public function parseHeading ($i, $v) {
        $split = explode('</h'.$i.'>', $v);
        $text = trim($split[0]);
        $content = trim($split[1]);

        $anchor = explode('(#', $text);

        if (!empty($anchor[1])) {
            $text = $anchor[0];
            $anchor = trim($anchor[1]);
            $anchor = substr($anchor, 0, -1);
            $anchor = preg_replace('/[^ \w-]/', '', $anchor);
        }
        else {
            $anchor = '';
        }

        $heading = '<h'.$i.' id="'.$anchor.'">'.$text.'</h'.$i.'>';
        $link = '<a href="#'.$anchor.'">'.$text.'</a>';

        return [
            'link' => $link,
            'text' => $text,
            'anchor' => $anchor,
            'heading' => $heading,
            'content' => $content
        ];
    }
}
