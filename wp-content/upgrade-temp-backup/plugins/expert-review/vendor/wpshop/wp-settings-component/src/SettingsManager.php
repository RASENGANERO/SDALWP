<?php

namespace Wpshop\SettingApi;

use Iterator;
use Wpshop\SettingApi\OptionField\FormFieldInterface;
use Wpshop\SettingApi\Section\SectionInterface;
use Wpshop\SettingApi\SettingsPage\SettingsPageInterface;

class SettingsManager
{
    /**
     * @var Iterator|SettingsProviderInterface[]
     */
    protected $providers;

    /**
     * @var SettingsPageInterface[]
     */
    protected $submenus = [];

    /**
     * SettingsManager constructor.
     *
     * @param Iterator $providers
     */
    public function __construct(Iterator $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return void
     */
    public function init()
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_media();
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery');
        });

        add_action('admin_menu', function () {
            foreach ($this->providers as $provider) {
                $submenu = $provider->getSettingsSubmenu();

                if (!$submenu instanceof SettingsPageInterface) {
                    throw new \RuntimeException('');
                }

                add_submenu_page(
                    $submenu->getParentSlug(),
                    $submenu->getPageTitle(),
                    $submenu->getMenuTitle(),
                    $submenu->getCapability(),
                    $submenu->getMenuSlug(),
                    [$submenu, 'render']
                );
                $this->submenus[spl_object_hash($provider)] = $submenu;
            }
        });

        add_action('admin_init', function () {
            foreach ($this->submenus as $submenu) {
                $this->setupSections($submenu->getSections());
            }
        });
    }

    /**
     * @param SectionInterface[] $sections
     *
     * @return void
     */
    protected function setupSections(array $sections)
    {
        foreach ($sections as $section) {

            // init option storage
            $section->getOptionStorage()->save();

            add_settings_section(
                $section->getId(),
                $section->getTitle(),
                $section->getRenderCallback(),
                $section->getId()
            );

            foreach ($section->getFields() as $field) {
                if (!$field->isEnabled()) {
                    continue;
                }

                $id = "{$section->getId()}[{$field->getName()}]";
                $value = $section->getOptionStorage()[$field->getName()];

                add_settings_field(
                    $id,
                    $field->getLabel(),
                    function () use ($field, $id, $value) {
                        $field->render($id, null, $value);
                    },
                    $section->getId(),
                    $section->getId()
                );
            }

            register_setting($section->getId(), $section->getId(), function ($options) use ($section) {
                foreach ($options as $name => $value) {
                    if (($field = $section->getFieldByName($name)) && $field instanceof FormFieldInterface) {

                        if (!$field->isEnabled()) {
                            unset($options[$name]);
                            continue;
                        }

                        $callback = apply_filters(
                            'wpshop_settings_sanitize_callback',
                            $field->getSanitizeCallback(), $value, $section
                        );

                        if ($callback) {
                            if (null === ($value = call_user_func($callback, $value))) {
                                unset($options[$name]);
                            } else {
                                $options[$name] = $value;
                            }
                        }
                    }
                }

                return $options;
            });
        }
    }
}
