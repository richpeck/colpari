/* renamed version of boostrap 3.0.0 button.js */

+function ($) { "use strict";

  // BUTTON PUBLIC CLASS DEFINITION
  // ==============================

  var TWButton = function (element, options) {
    this.$element = $(element)
    this.options  = $.extend({}, TWButton.DEFAULTS, options)
  }

  TWButton.DEFAULTS = {
    loadingText: 'loading...'
  }

  TWButton.prototype.setState = function (state) {
    var d    = 'disabled'
    var $el  = this.$element
    var val  = $el.is('input') ? 'val' : 'html'
    var data = $el.data()

    state = state + 'Text'

    if (!data.resetText) $el.data('resetText', $el[val]())

    $el[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d);
    }, 0)
  }

  TWButton.prototype.toggle = function () {
    var $parent = this.$element.closest('[data-toggle="buttons"]')

    if ($parent.length) {
      var $input = this.$element.find('input')
        .prop('checked', !this.$element.hasClass('active'))
        .trigger('change')
      if ($input.prop('type') === 'radio') $parent.find('.active').removeClass('active')
    }

    this.$element.toggleClass('active')
  }


  // BUTTON PLUGIN DEFINITION
  // ========================

  var old = $.fn.twbutton

  $.fn.twbutton = function (option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.twbutton')
      var options = typeof option == 'object' && option

      if (!data) $this.data('bs.twbutton', (data = new TWButton(this, options)))

      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }

  $.fn.twbutton.Constructor = TWButton


  // BUTTON NO CONFLICT
  // ==================

  $.fn.twbutton.noConflict = function () {
    $.fn.twbutton = old
    return this
  }


  // BUTTON DATA-API
  // ===============

  $(document).on('click.bs.twbutton.data-api', '[data-toggle^=twbutton]', function (e) {
    var $btn = $(e.target)
    if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
    $btn.twbutton('toggle')
    e.preventDefault()
  })

}(window.jQuery);