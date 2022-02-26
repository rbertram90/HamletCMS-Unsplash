<?php

namespace HamletCMS\Unsplash;

class Module {

    public function imageUploader($args) {
        $args['tabs'][] = [
            'label' => 'Search Unsplash',
            'url' => '/cms/unsplash/search/' . $args['blog']->id,
        ];
    }

}
