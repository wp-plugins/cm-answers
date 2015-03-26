<div class="my_meta_control cm-help-items-options">

    <?php
    wp_print_styles('editor-buttons');

    ob_start();
    wp_editor('', 'content', array(
        'dfw'           => true,
        'editor_height' => 1,
        'tinymce'       => array(
            'resize'             => true,
            'add_unload_trigger' => false,
        ),
    ));
    $content = ob_get_contents();
    ob_end_clean();

    $args = array(
        'post_type'         => 'page',
        'show_option_none'  => CMOnBoarding::__('None'),
        'option_none_value' => '',
    );
    ?>

    <label>Show on every page</label>
    <p>
        <?php $mb->the_field('cm-help-item-show-allpages'); ?>
        <input type="hidden" name="<?php $mb->the_name(); ?>" value="0"/>
        <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1" <?php checked('1', $metabox->get_the_value()); ?> class="<?php $mb->the_name(); ?>"/>
        <span>If this checkbox is selected then then this Help Item will be displayed on each post and page of your website</span>
    </p>

    <p class="meta-save"><button type="submit" class="button-primary" name="save"><?php _e('Update'); ?></button></p>

</div>