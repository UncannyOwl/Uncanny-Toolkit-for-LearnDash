//$.noConflict();
jQuery(document).ready(function ($) {


  //Reset save options button if Options changed
  $('.uo_settings_options').on('change keyup', '.uo_settings_form_field', function () {
    var save_settings_button = $(this).closest('.uo_settings_options').find('.uo_save_settings')
    save_settings_button.html('Save Settings')
    save_settings_button.css('background', '#A9A9A9')
  })

  // SAVE SETTINGS
  $('.uo_save_settings').on('click', function (e) {
    e.preventDefault()
    $('.uo_settings_options').hide('fast')
    $('.sk-folding-cube').show('fast')

    var button = $(this)
    var settings_class = $(this).closest('.uo_settings').attr('id')
    var options = $(this).closest('.uo_settings_options').find('input, select').serializeArray()
    $(this).closest('.uo_settings_options').find('textarea').each(function () {
      var textarea_id = $(this).attr('id')
      var textrea_content = get_tinymce_content(textarea_id)
      options.push({name: textarea_id, value: textrea_content})
    })
    var data = {
      'action': 'settings_save',
      'class': settings_class,
      'options': options

    }
    //console.log(data)
    $.post(ajaxurl, data, function (response) {

      if ('success' === response) {
        $(button).html('Options Saved')
        $(button).css('background', '#238b23')
      } else {
        $(button).html('Error: Check Console')
        $(button).css('background', '#ac2525')
        //console.log(response);
      }

      //$('.sk-folding-cube').delay(1500).hide('slow');
      $('.sk-folding-cube').hide()
      //$('.uo_settings_options').delay(2000).show('slow');
      $('.uo_settings_options').show('fast')

      setTimeout(
        function () {
          $(button).html('Save Settings')
          $(button).removeAttr('style')
        }, 5000)

    })

  })

  // LOAD SETTINGS
  //$('.uo_settings_link').live('click', function(e) {
  $('#features, ult-directory-moduleult-directory-module-settings').delegate('a', 'click', function (e) {
    //console.log($(this).attr('href'));
    var settings_class = $(this).attr('href')
    settings_class = settings_class.replace('#', '')
    var settings_container = $('#' + settings_class).find('.uo_settings_options')

    // Show Spinner
    $('.sk-folding-cube').show()
    //Hide Setting UI
    settings_container.hide()

    var data = {
      'action': 'settings_load',
      'class': settings_class
    }

    //console.log(data);
    $.post(ajaxurl, data, function (response) {
      //console.log(response);
      var saved_options = JSON.parse(response)

      $.each(saved_options, function (options_index, option) {
        var element = $('#' + settings_class).find('[name="' + option['name'] + '"]')
        var option_value = remove_slashes_from_strong(option['value'])
        //console.log(option_value);
        if (element.is('input[type="text"]')) {
          element.val(option_value)
        }

        if (element.is('textarea')) {
          element.val(option_value)
        }

        if (element.is('input[type="color"]')) {
          element.val(option_value)
        }

        if (element.is('input[type="checkbox"]')) {
          if ('on' === option_value) {
            element.prop('checked', true)
          } else {
            element.prop('checked', false)
          }
        }

        if (element.is('input[type="radio"]')) {

          $.each(element, function (radio_index, radio) {
            if (option_value === $(radio).val()) {
              $(radio).prop('checked', true)
            }
          })

        }

        if (element.is('select')) {
          element.val(option_value)
        }

      })
      // Hide Spinner
      //$('.sk-folding-cube').delay(1000).hide('slow');
      $('.sk-folding-cube').hide('fast')
      // Show Settings Hide
      //settings_container.delay(1000).show('slow')
      settings_container.show('fast')
      settings_container.find('.uo-color-picker').wpColorPicker()
    })
  })

  toolkit_view()

  function toolkit_view () {

    console.log('runnning')
    let actual_view = 'grid'
    if (localStorage.getItem('uoToolkitGrid')) {
      actual_view = localStorage.getItem('uoToolkitGrid')
    }

    let features = $('#features')

    if ('list' === actual_view) {
      $('.switch-btn').removeClass('selected')
      $('.switch-btn.list-view').addClass('selected')

      features.removeClass('grid-view list-view')
      features.addClass(actual_view + '-view')
    }

    $('.switch-btn').click(function () {
      var new_view = $(this).hasClass('grid-view') ? 'grid' : 'list'
      if (actual_view !== new_view) {
        actual_view = new_view

        localStorage.setItem('uoToolkitGrid', new_view)

        $('.switch-btn').removeClass('selected')
        $(this).addClass('selected')

        features.removeClass('grid-view list-view')
        features.addClass(new_view + '-view')
      }
    })
  }

  function remove_slashes_from_strong (string) {
    return string.replace(new RegExp('\\\\', 'g'), '')
  }

  function get_tinymce_content (id) {
    var content
    var inputid = id
    if (is_tinyMce_active_func()) {
      var editor = tinyMCE.get(inputid)
    }
    var textArea = jQuery('textarea#' + inputid)
    if (textArea.length > 0 && textArea.is(':visible')) {
      content = textArea.val()
    } else {
      content = editor.getContent()
    }
    return content
  }

  function is_tinyMce_active_func () {

    var tinymceActive = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()
    return tinymceActive
  }

})
