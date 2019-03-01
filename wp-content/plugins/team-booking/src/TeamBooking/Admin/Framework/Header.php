<?php

namespace TeamBooking\Admin\Framework;

class Header implements Element
{
    private $plugin_data;
    private $active_tab_slug;

    private static $elements = array(
        'tabs'      => array(),
        'main_text' => 'Hello!',
        'logo'      => array(
            'img_url'  => '',
            'url'      => '',
            'alt_text' => ''
        ),
        'version'   => '',
        'docs'      => array(
            'text' => 'Docs',
            'url'  => ''
        )
    );

    public function setPluginData($path)
    {
        $this->plugin_data = get_plugin_data($path);
        self::$elements['version'] = TEAMBOOKING_VERSION;
        self::$elements['docs']['url'] = $this->plugin_data['PluginURI'];
    }

    public function setMainText($text)
    {
        self::$elements['main_text'] = $text;
    }

    public function addTab($slug, $text, $dashicon_class, $active = FALSE)
    {
        self::$elements['tabs'][ $slug ] = array(
            'text'     => $text,
            'dashicon' => $dashicon_class
        );
        if ($active) {
            $this->active_tab_slug = $slug;
        }
    }

    public function addLogo($img_url, $url, $alt_text)
    {
        self::$elements['logo']['img_url'] = $img_url;
        self::$elements['logo']['url'] = $url;
        self::$elements['logo']['alt_text'] = $alt_text;
    }

    public function render()
    {
        ?>
        <header class="tbk-header">
            <div class="tbk-version-docs">v. <?= self::$elements['version'] ?> |
                <a href="<?= admin_url('admin.php?page=team-booking&whatsnew') ?>"><?= esc_html__("What's new", 'team-booking') ?></a>
                |
                <a href="<?= self::$elements['docs']['url'] ?>/docs"
                   target="_blank"><?= self::$elements['docs']['text'] ?>
                </a>
            </div>
            <a href="<?= self::$elements['logo']['url'] ?>" target="_blank" class="tbk-logo"
               title="<?= self::$elements['logo']['alt_text'] ?>">
            </a>
            <h2 class="tbk-heading"><?= self::$elements['main_text'] ?></h2>
            <nav class="tbk-nav-horizontal">
                <ul>
                    <?php
                    foreach (self::$elements['tabs'] as $slug => $tab) {
                        $page_slug = $slug === 'overview' ? 'team-booking' : 'team-booking-' . $slug;
                        ?>
                        <li>
                            <a href="<?= admin_url('admin.php?page=' . $page_slug) ?>" <?= ($slug === $this->active_tab_slug) ? 'class="active"' : '' ?>>
                                    <span class="dashicons <?= $tab['dashicon'] ?>"
                                          style="line-height: inherit;"></span>
                                <?= esc_html($tab['text']) ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </header>
        <?php
    }

}