<?php

    $user_id = 'rich_webb';

    if (!file_exists( $user_id.'/images')) {
        mkdir($user_id.'/images', 0777, true);
    }
    if (!file_exists( $user_id.'/videos')) {
        mkdir($user_id.'/videos', 0777, true);
    }

