<?php

namespace Wpshop\SettingApi\SettingsPage;

use Wpshop\SettingApi\Section\Section;

class TabSettingsPage implements SettingsPageInterface
{
    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * @var string
     */
    protected $menuTitle;

    /**
     * @var string
     */
    protected $capability;

    /**
     * @var string
     */
    protected $menuSlug;

    /**
     * @var Section[]
     */
    protected $sections = [];

    /**
     * @var string
     */
    protected $parentSlug = 'options-general.php';

    /**
     * @var callable|null
     */
    protected $beforeRenderCallback;

    /**
     * @var callable|null
     */
    protected $afterRenderCallback;

    /**
     * SettingsSubmenu constructor.
     *
     * @param string $pageTitle
     * @param string $menuTitle
     * @param string $capability
     * @param string $menuSlug
     */
    public function __construct($pageTitle, $menuTitle, $capability, $menuSlug)
    {
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->capability = $capability;
        $this->menuSlug = $menuSlug;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @return string
     */
    public function getMenuTitle()
    {
        return $this->menuTitle;
    }

    /**
     * @return string
     */
    public function getCapability()
    {
        return $this->capability;
    }

    /**
     * @return string
     */
    public function getMenuSlug()
    {
        return $this->menuSlug;
    }

    /**
     * @return void
     */
    public function render()
    {
        echo '<div class="wrap">';
        if ($title = $this->getPageTitle()) {
            printf('<h1>%s</h1>', $this->pageTitle);
        }
        if ($this->beforeRenderCallback) {
            call_user_func($this->beforeRenderCallback);
        }
        $this->renderNav();
        $this->renderForms();
        if ($this->afterRenderCallback) {
            call_user_func($this->afterRenderCallback);
        }
        echo '</div>';
        $this->renderJs();
    }

    /**
     * @return void
     */
    protected function renderNav()
    {
        printf('<h2 class="nav-tab-wrapper js-nav-tab-wrapper" data-tab-namespace="%s">', $this->menuSlug);
        foreach ($this->sections as $section) {
            printf('<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $section->getId(), $section->getTitle());
        }
        echo '</h2>';

    }

    /**
     * @return void
     */
    protected function renderForms()
    {
        echo '<div class="metabox-holder">';
        foreach ($this->sections as $section) {
            printf('<div id="%s" class="group" style="display: none;">', $section->getId());
            echo '<form method="post" action="options.php">';
            settings_fields($section->getId());
            do_settings_sections($section->getId());
            submit_button();
            echo '</form>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * @return void
     */
    protected function renderJs()
    {
        echo <<<HTML
<script type="text/javascript">
	jQuery(function($) {
		$('.wp-color-picker-field').wpColorPicker();
		$('.group').hide();
		
		var tabNamespace = 'activetab:' + $('.js-nav-tab-wrapper').data('tab-namespace');
		var activetab = '';
		if (typeof(localStorage) != 'undefined' ) {
            activetab = localStorage.getItem(tabNamespace);
        }
		if (activetab && $(activetab).length ) {
            $(activetab).fadeIn(100);
        } else {
            $('.group:first').fadeIn(100);
        }
		$('.group .collapsed').each(function(){
            $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function(){
                if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
            });
        });
		
		if (activetab && $(activetab + '-tab').length ) {
            $(activetab + '-tab').addClass('nav-tab-active');
        }
        else {
            $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
        }
        $('.nav-tab-wrapper a').click(function(evt) {
            $('.nav-tab-wrapper a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active').blur();
            var clicked_group = $(this).attr('href');
            if (typeof(localStorage) != 'undefined' ) {
                localStorage.setItem(tabNamespace, $(this).attr('href'));
            }
            $('.group').hide();
            $(clicked_group).fadeIn(100);
            evt.preventDefault();
        });
        
        $('.wpshop-settings-browse').on('click', function (event) {
            event.preventDefault();

            var self = $(this);

            var file_frame = wp.media.frames.file_frame = wp.media({
                title: self.data('uploader_title'),
                button: {
                    text: self.data('uploader_button_text'),
                },
                multiple: false
            });

            file_frame.on('select', function () {
                attachment = file_frame.state().get('selection').first().toJSON();

                self.prev('.wpshop-settings-url').val(attachment.url);
            });

            file_frame.open();
        });
	})
</script>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function addSection(Section $section)
    {
        $this->sections[] = $section;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @see https://wp-kama.ru/function/add_submenu_page
     *
     * @return string
     */
    public function getParentSlug()
    {
        return $this->parentSlug;
    }

    /**
     * @param string $parentSlug
     *
     * @return $this
     */
    public function setParentSlug($parentSlug)
    {
        $this->parentSlug = $parentSlug;

        return $this;
    }

    /**
     * @param callable|null $callback
     *
     * @return $this
     */
    public function setBeforeRenderCallback($callback)
    {
        $this->beforeRenderCallback = $callback;

        return $this;
    }

    /**
     * @param callable|null $callback
     *
     * @return $this
     */
    public function setAfterRenderCallback($callback)
    {
        $this->afterRenderCallback = $callback;

        return $this;
    }
}
