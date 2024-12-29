<?php

namespace Wpshop\SettingApi\SettingsPage;

use Wpshop\SettingApi\Section\Section;

interface SettingsPageInterface
{
    /**
     * @return string
     */
    public function getPageTitle();

    /**
     * @return string
     */
    public function getMenuTitle();

    /**
     * @return string
     */
    public function getCapability();

    /**
     * @return string
     */
    public function getMenuSlug();

    /**
     * @return void
     */
    public function render();

    /**
     * @param Section $section
     *
     * @return $this
     */
    public function addSection(Section $section);

    /**
     * @return Section[]
     */
    public function getSections();

    /**
     * @see https://wp-kama.ru/function/add_submenu_page
     *
     * @return string
     */
    public function getParentSlug();
}
