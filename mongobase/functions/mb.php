<?php

function var_dumped($data = array(), $exit = true, $title = false)
{
    echo '<div style="display:block; postiion:relative; padding:20px">';
	if($title) echo '<br /><strong>'.$title.'</strong><br />';
    echo '<pre>';
    print_r($data);
    echo '</pre><br />';
	echo '</div>';
    if($exit) exit;
}
