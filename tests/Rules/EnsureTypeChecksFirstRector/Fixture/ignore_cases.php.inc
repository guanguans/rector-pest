<?php

expect($post)->toBeInstanceOf(Post::class)
    ->and($user)->toBeInstanceOf(User::class)
    ->and($comment)->toBeInstanceOf(Comment::class);
