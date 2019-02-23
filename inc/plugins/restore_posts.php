<?php
if(!defined("IN_MYBB"))
{
    die("Direct access to this file is not allowed.");
}

$my_plugins = $cache->read("plugins");
if(array_key_exists("restore_posts", $my_plugins['active']))
{
    require_once "restore_posts/hooks.php";
}

function restore_posts_info()
{
    global $lang;

    $lang->load("restore_posts");

    return array(
        "name"	=> $lang->restore_posts_title,
        "description" => $lang->restore_posts_description . " " . $lang->restore_posts_description_paypal,
        "author" => "Mark Janssen",
        "version" => "1.0",
        "codename" 	=> "restore_posts",
        "compatibility"	=> "18*"
    );
}

function restore_posts_install()
{
}

function restore_posts_is_installed()
{
    global $cache;
    $my_plugins = $cache->read("plugins");
    if(array_key_exists("restore_posts", $my_plugins['active']))
    {
        return true;
    }
    return false;
}

function restore_posts_activate()
{
    require_once "restore_posts/templates.php";
    restore_posts_templates_install();
}

function restore_posts_deactivate()
{
    require_once "restore_posts/templates.php";
    restore_posts_templates_uninstall();
}

function restore_posts_uninstall()
{
}
