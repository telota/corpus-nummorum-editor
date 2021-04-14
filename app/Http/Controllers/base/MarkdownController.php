<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use MarkConverter;
use Request;

class MarkdownController extends Controller {

    public function readme () {
        $helper = new MdMethods();
        $data = $helper->provideData('README.md');

        return view('base.markdown_file', $data);
    }

    public function license () {
        $helper = new MdMethods();
        $data = $helper->provideData('LICENSE.md');

        return view('base.markdown_file', $data);
    }

    public function licensing () {
        $helper = new MdMethods();
        $data = $helper->provideData('LICENSE.md');

        return view('base.markdown_file', $data);
    }

    public function changelog () {
        $helper = new MdMethods();
        $data = $helper->provideData('CHANGELOG.md');

        $data['heading'] = 'test';
        $data['introduction'] = 'test';

        return view('base.markdown_file', $data);
    }

    public function documentation () {
        $helper = new MdMethods();
        $data = $helper->provideData('DOCUMENTATION.md');

        $data['heading'] = 'test';
        $data['introduction'] = 'test';

        return view('base.markdown_file', $data);
    }
}


class MdMethods {

    public function provideData ($file) {
        $language = substr(Request::server('HTTP_ACCEPT_LANGUAGE'), 0, 2) === 'de' ? 'de' : 'en';

        $parsed = $this->getHtmlFromFile($file, $language);
        $processed = $this->processContent($parsed['html']);

        return array_merge($parsed, $processed);
    }

    public function getHtmlFromFile ($file, $language) {
        $full = base_path().'/'.$file;
        $date_format = $language === 'de' ? "d.m.Y" : "m/d/Y";
        $date = date($date_format, filemtime($full));

        $markdown = file_get_contents($full);
        $converter = new MarkConverter();
        $html = $converter->convertToHtml($markdown);

        return [
            'html' => $html,
            'date' => $date
        ];
    }

    public function processContent ($content) {
        $toc = [];
        $heading = null;

        $content = preg_replace_callback(
            '|<h[^>]+>(.*)</h[^>]+>|iU', // regex for any h-elements
            function ($h) use (&$toc, &$heading) {
                $c = 1;
                $i = intval(substr($h[0], 2, 1)); // extract index of h-tag
                $anchors = [];

                $text = trim($h[1]);
                $split = explode('(#', $text); // anchors are written as (#anchor) attached to the heading-textcontent
                $text = trim($split[0]);

                if ($i === 1) {
                    $heading = $text; // extract/remove h1
                }
                else {
                    $anchor = empty($split[1]) ? $text : substr($split[1], 0, -1); // check if an anchor was set in md

                    $anchor = strtolower(str_replace(' ', '-', $anchor));
                    $anchor = preg_replace('/[^\w-]/', '', $anchor);

                    if (in_array($anchor, $anchors)) { // check if anchor was already set, add increament if yes
                        $anchor .= '-'.$c;
                        ++$c;
                    }

                    $anchors[] = $anchors;

                    $replace = '<h'.$i.' id="'.$anchor.'" class="md-h'.$i.'">'.$text.'</h'.$i.'>'; // Heading in article (styling s. class in blade)
                    $link = '<a href="#'.$anchor.'" class="md-l'.$i.'">'.$text.'</a><br/>'; // Link in TOC (styling s. class in blade)

                    $toc[] = $link;
                    return $replace;
                }
            },
            $content
        );
        $content = trim($content);

        return [
            'toc' => $toc,
            'heading' => $heading,
            'content' => $content
        ];
    }
}
