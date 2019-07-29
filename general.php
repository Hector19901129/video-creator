<?php

    $user_id = 'rich_webb';
    $vid_dir = 'users';

    if (!file_exists( $vid_dir.'/'.$user_id.'/images')) {
        mkdir($vid_dir.'/'.$user_id.'/images', 0777, true);
    }

    if (!file_exists( $vid_dir.'/'.$user_id.'/videos')) {
        mkdir($vid_dir.'/'.$user_id.'/videos', 0777, true);
    }

    if (!file_exists( $vid_dir.'/'.$user_id.'/temp')) {
        mkdir($vid_dir.'/'.$user_id.'/temp', 0777, true);
    }


