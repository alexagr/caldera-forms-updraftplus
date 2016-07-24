<div class="caldera-config-group">
    <label><?php echo __('Filename', 'cf-updraft'); ?> </label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config required" name="{{_name}}[filename]" value="{{filename}}">
    </div>
</div>

<div class="caldera-config-group">
    <label><?php echo __('Directory', 'cf-updraft'); ?> </label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config required" name="{{_name}}[directory]" value="{{directory}}">
    </div>
</div>

<div class="caldera-config-group">
    <label><?php echo __('Title', 'cf-updraft'); ?> </label>
    <div class="caldera-config-field">
        <input type="text" class="block-input field-config required" name="{{_name}}[title]" value="{{title}}">
    </div>
</div>

<hr /> 

<div class="caldera-config-group">
    <label><?php echo __('Header', 'cf-updraft'); ?> </label>
    <div class="caldera-config-field">
        <textarea class="block-input field-config required magic-tag-enabled" name="{{_name}}[header]">{{#if header}}{{header}}{{else}}<?php _e("<html>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n<body>", 'cf-updraft'); ?>{{/if}}</textarea>
    </div>
</div>

<div class="caldera-config-group">
    <label><?php echo __('Footer', 'cf-updraft'); ?> </label>
    <div class="caldera-config-field">
        <textarea class="block-input field-config required magic-tag-enabled" name="{{_name}}[footer]">{{#if footer}}{{footer}}{{else}}<?php _e("</body>\r\n</html>", 'cf-updraft'); ?>{{/if}}</textarea>
    </div>
</div>