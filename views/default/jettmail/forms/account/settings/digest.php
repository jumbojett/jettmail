<div class="elgg-module elgg-module-info">


    <div class="elgg-head">
        <h3><?php echo elgg_echo('jettmail:digest_header'); ?></h3>
    </div>
    <p><input type="checkbox" name="digest-input"
              id="digest-input" <?php if ($_SESSION['user']->digest == 'on') echo 'checked' ?>/> <?php echo elgg_echo('jettmail:digest_label'); ?>
    </p>
</div>
<?php
echo elgg_view('input/hidden', array('name' => 'language', 'value' => 'english'));
