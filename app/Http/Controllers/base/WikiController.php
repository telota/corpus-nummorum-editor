<?php

namespace App\Http\Controllers\base;

use App\Http\Controllers\Controller;
use MarkConverter;

class WikiController extends Controller {

    public function index () {
        return view('base.wiki.content', [
            'content'   => 'no content yet'
        ]);
    }

    public function readme () {
        $helper = new WikiMethods();
        $content = $helper->provideFile('README.md');

        return view('base.markdown_file', [
            'header' => 'readme',
            'content' => $content
        ]);
    }

    public function license () {
        $helper = new WikiMethods();
        $content = $helper->provideFile('LICENSE.md');

        return view('base.markdown_file', [
            'header' => 'license',
            'content' => $content
        ]);
    }
}


class WikiMethods {

    public function provideFile ($file) {
        $markdown = file_get_contents(base_path().'/'.$file);

        $converter = new MarkConverter();
        $html = $converter->convertToHtml($markdown);

        return $html;
    }
}
