<p>
    <a href="<?php esc_attr_e($baseurl) ?>" class="btn btn-secondary"><?php _e("Go Back", "adverts") ?></a>
    <a href="<?php esc_attr_e(get_post_permalink( $post_id )) ?>" class="btn btn-secondary"><?php _e("View Ad", "adverts") ?></a>
</p>

<?php adverts_flash( $adverts_flash ) ?>

<form action="" method="post">
    <fieldset>

        <?php foreach($form->get_fields( array( "type" => array( "adverts_field_hidden" ) ) ) as $field): ?>
        <?php call_user_func( adverts_field_get_renderer($field), $field) ?>
        <?php endforeach; ?>

        <?php foreach($form->get_fields( array( "exclude" => array( "account" ) ) ) as $field):
        if (!isset($field['class'])) { $field['class'] = ''; }
        $field['class'] .= ' form-control'; ?>

        <div class="form-group <?php esc_attr_e( str_replace("_", "-", $field["type"] ) . " adverts-field-name-" . $field["name"] ) ?> <?php if(adverts_field_has_errors($field)): ?>adverts-field-error<?php endif; ?>">

            <?php if($field["type"] == "adverts_field_header"): ?>
            <span class='h5'><?php esc_html_e($field["label"]) ?></span>
            <?php else: ?>

            <label for="<?php esc_attr_e($field["name"]) ?>">
                <?php esc_html_e($field["label"]) ?>
                <?php if(adverts_field_has_validator($field, "is_required")): ?>
                <span class="adverts-form-required text-danger">*</span>
                <?php endif; ?>
            </label>

            <?php call_user_func( adverts_field_get_renderer($field), $field) ?>

            <?php endif; ?>

            <?php if(adverts_field_has_errors($field)): ?>
            <ul class="adverts-field-error-list text-danger">
                <?php foreach($field["error"] as $k => $v): ?>
                <li><?php esc_html_e($v) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>

        <div class='form-group'>

            <input type="submit" name="submit" value="<?php _e("Update", "adverts") ?>" class='btn btn-primary' />
        </div>

    </fieldset>
</form>
