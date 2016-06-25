<?php

if (!function_exists('infy_tab')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $spaces
     *
     * @return string
     */
    function infy_tab($spaces = 4)
    {
        return str_repeat(' ', $spaces);
    }
}

if (!function_exists('infy_tabs')) {
    /**
     * Generates tab with spaces.
     *
     * @param int $tabs
     * @param int $spaces
     *
     * @return string
     */
    function infy_tabs($tabs, $spaces = 4)
    {
        return str_repeat(infy_tab($spaces), $tabs);
    }
}

if (!function_exists('infy_nl')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     *
     * @return string
     */
    function infy_nl($count = 1)
    {
        return str_repeat(PHP_EOL, $count);
    }
}

if (!function_exists('infy_nls')) {
    /**
     * Generates new line char.
     *
     * @param int $count
     * @param int $nls
     *
     * @return string
     */
    function infy_nls($count, $nls = 1)
    {
        return str_repeat(infy_nl($nls), $count);
    }
}

if (!function_exists('infy_nl_tab')) {
    /**
     * Generates new line char.
     *
     * @param int $lns
     * @param int $tabs
     *
     * @return string
     */
    function infy_nl_tab($lns = 1, $tabs = 1)
    {
        return infy_nls($lns).infy_tabs($tabs);
    }
}
