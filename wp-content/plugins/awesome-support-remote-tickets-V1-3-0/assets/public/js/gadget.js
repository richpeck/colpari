var wpasGadget = {

  init: function (data) {
    this.data = data;
    var xhttp = new XMLHttpRequest();

    xhttp.open('GET', this.data.url + 'wpas-api/v1/remote-tickets/' + this.data.gadgetID);
    xhttp.setRequestHeader('Content-type', 'application/json');

    xhttp.onload = function () {
      var response = JSON.parse(xhttp.responseText);

      if (undefined !== response.settings) {
        wpasGadget.data = response.settings;

	    if ( 'on' != wpasGadget.data.disable ) {
		  if (wpasGadget.showForm()) {
            wpasGadget.createDOM();
          }
		}
      }

    };

    xhttp.send();
  },

  /**
   * Create the ticket dom
   */
  createDOM: function () {
    var style = this.getCSS();
    this.button = this.createElement('button', {id: 'wpas-button', class: 'wpas-button', 'data-action': 'submit'});
    this.wrapper = this.createElement('div', {class: 'wpas-wrapper'});

    this.button.innerHTML = this.data.buttonText;
	
    this.maybeHideHelpButton();

    this.wrapper.setAttribute('class', 'wpas-wrapper');
    this.wrapper.appendChild(style);
    this.wrapper.appendChild(this.button);

    this.getStylesheet();
    this.createForm();

    this.button.addEventListener('click', this.handleButtonClick);
    document.body.appendChild(this.wrapper);
  },

  /**
   * Create the ticket form
   */
  createForm: function() {
    if ( undefined !== this.formWrap ) {
      this.wrapper.removeChild(this.formWrap);
    }

    this.close    = this.createElement('button', {id: 'wpas-close', class: 'wpas-close'});
    this.formWrap = this.getTicketForm();

    this.close.innerHTML = 'x';

    this.form.appendChild(this.close);
    this.wrapper.appendChild(this.formWrap);

    this.close.addEventListener('click', this.handleClose);
    this.formWrap.addEventListener('click', this.handleWrapClick);
    this.form.addEventListener('submit', this.handleTicketSubmit);
  },

  /** Event listeners ************************/

  /**
   * Handle click of the ticket form wrap area
   *
   * @param e
   */
  handleWrapClick: function (e) {
    if (e.target !== wpasGadget.formWrap) {
      return;
    }

    wpasGadget.handleClose(e);
  },

  handleClose: function (e) {
    if (undefined !== e) {
      e.preventDefault();
    }

    wpasGadget.button.setAttribute('data-action', 'submit');
    wpasGadget.button.innerHTML = wpasGadget.data.buttonText;
    wpasGadget.formWrap.setAttribute('style', 'display:none;');
  },

  /**
   * Handle button click action
   */
  handleButtonClick: function (e) {
    var button = wpasGadget.button;
    var form = wpasGadget.formWrap;
    var action = button.getAttribute('data-action');

    if ('submit' === action || 'create' === action) {
      button.setAttribute('data-action', 'cancel');
      button.innerHTML = wpasGadget.data.buttonCancelText;
      form.setAttribute('style', 'display:block;');
    } else {
      button.setAttribute('data-action', 'submit');
      button.innerHTML = wpasGadget.data.buttonText;
      form.setAttribute('style', 'display:none;');
    }
  },

  /**
   * Handle ticket form submit
   * @param e
   */
  handleTicketSubmit: function (e) {
    var name, field, errors = [], values = {};
    e.preventDefault();

    var fields = Object.keys(wpasGadget.data.labels);
    var form = wpasGadget.form;

    fields.push('gadget_id');

    for (var i = 0; i < fields.length; i++) {
      name = fields[i];

      field = form.querySelector('[name=' + name + ']');

      if ('file' === name) {
        values[name] = field.files[0];
      } else {
        values[name] = field.value;
      }

      if ('file' !== name && !Boolean(values[name])) {
        errors.push(wpasGadget.data.errorText.replace('%s', wpasGadget.data.labels[name]));
      }
    }

    if (wpasGadget.handleFormErrors(errors)) {
      wpasGadget.submitTicket(values);
    }
  },

  /** Helper functions ********************************/

	/**
   * Determines whether the form should show on this page
   *
	 * @returns {boolean}
	 */
  showForm: function() {
    if (! Array.isArray(this.data.pageMatches)){
      return true;
    }

    if (! this.data.pageMatches.length) {
      return true;
    }

    var location = window.location.href;
    var include  = 'include' === this.data.pageMatchesSetting;

    for (var i = 0; i < this.data.pageMatches.length; i ++) {
      if (-1 !== location.indexOf(this.data.pageMatches[i])) {
        return include;
      }
    }

    return ! include;
  },

  /**
   * Create the ticket form
   *
   * @returns {*}
   */
  getTicketForm: function () {
    var field, label, labelText, id, name, p;
	var formHeader, formFooter;
    var fields = Object.keys(this.data.labels);
	
	/* Create outer layer div that 'fades' the entire screen background */
    var wrapper = this.createElement('div', {class: 'wpas-ticket-wrapper', id: 'wpas-ticket-wrapper'});

	/* Create main form wrapper and area for messages */
    this.form = this.createElement('form', {class: 'wpas-ticket-form', id: 'wpas-ticket-form'});
    this.formMessages = this.createElement('div',
      {class: 'wpas-ticket-form-messages', id: 'wpas-ticket-form-messages'});

	/* Create the area for the left text header if its defined */ 
	if ( this.data.leftHeaderText ) {
		formHeaderLeft = this.createElement('p', {class: 'wpas-rt-form-header-text-wrap'});
		formHeaderLeft.innerHTML = this.data.leftHeaderText;
		this.form.appendChild(formHeaderLeft);	
	}
	
	/* Create the area for the right text header if its defined */ 	
	if ( this.data.rightHeaderText ) {	
		formHeaderRight = this.createElement('p', {class: 'wpas-rt-form-header-text-wrap'});
		formHeaderRight.innerHTML = this.data.rightHeaderText;
		this.form.appendChild(formHeaderRight);		
	}
	 
	/* Create individual field elements and add them to the form */	 
    for (var i = 0; i < fields.length; i++) {
      name = fields[i];
      labelText = this.data.labels[name];

      p = this.createElement('p', {class: name + '-wrap'});
      label = this.createElement('label', {for: 'wpas_' + name});
      label.innerHTML = labelText;
      field = this.getField(name);

      p.appendChild(label);
      p.appendChild(field);
      this.form.appendChild(p);
    }
	
	/* Create the area for pre-footer before the help/submit button if its defined */ 	
	if ( this.data.preFooterText ) {
		formPreFooter = this.createElement('p', {class: 'wpas-rt-form-footer-text-wrap'});
		formPreFooter.innerHTML = this.data.preFooterText;
		this.form.appendChild(formPreFooter);	
	}

	/* Create terms of service checkbox */
	if ( 'on' == this.data.enableTos && this.data.tosText ) {
		p = this.createElement('p', {class: 'tos-wrap'});
		ctos = this.createElement('input', {class: 'tos'});
		ctos.type = 'checkbox';
		ctos.id = 'tosChkbox';
		ctos.value = 1;
		ctos.required = true ;
		ctos.defaultvalue = 0 ;
		
		ltos = this.createElement('label', {class: 'label_tos'});
		ltos.setAttribute('for',ctos.id);
		ltos.setAttribute('id','tosLabel');
		ltos.innerHTML = this.data.tosText;

		p.appendChild(ctos);
		p.appendChild(ltos);
		this.form.appendChild(p);
	}	

	/* Create submit button */
    p = this.createElement('p', {class: 'submit-wrap'});
    var gadgetID = this.createElement('input', {type: 'hidden', name: 'gadget_id', value: this.data.gadgetID});
    var submit = this.createElement('input', {type: 'submit', id: 'wpas-ticket-submit', class: 'wpas-ticket-submit', value: this.data.buttonText});

    p.appendChild(gadgetID);
    p.appendChild(submit);
	
    this.form.appendChild(p);
	
	/* Create the area for footer if its defined */ 
	if ( this.data.footerText ) {
		formFooter = this.createElement('p', {class: 'wpas-rt-form-footer-text-wrap'});
		formFooter.innerHTML = this.data.footerText;
		this.form.appendChild(formFooter);	
	}	
	
	/* More form messages */
    this.form.appendChild(this.formMessages);
	
	/* The final form on top of the faded background wrapper */
    wrapper.appendChild(this.form);

    return wrapper;
  },

  /**
   * Handle the form errors
   *
   * @param errors
   * @returns {boolean}
   */
  handleFormErrors: function (errors) {
    var error, message;
    this.formMessages.innerHTML = '';

    if (!errors.length) {
      return true;
    }

    for (var i = 0; i < errors.length; i++) {
      error = this.createElement('p', {class: 'wpas-error'});
      message = errors[i];

      error.innerHTML = message;

      this.formMessages.appendChild(error);
    }

    return false;
  },

  /**
   * Handle form success message
   *
   * @param response
   */
  handleFormSuccess: function (response) {
    var button, message = this.createElement('p', {class: 'wpas-success'});
    this.formMessages.innerHTML = this.formWrap.innerHTML = '';
    this.form = this.createElement('div', {class: 'wpas-ticket-form', id: 'wpas-ticket-form'});

    button = this.createElement('button', {id: 'wpas-button-success', class: 'wpas-ticket-submit'});

    message.innerHTML = this.data.successText.replace('[ticket-url]', '<a href="' + response.link + '">' + response.link + '</a>');
    button.innerHTML = this.data.successButtonText;

    button.addEventListener('click', function(e) {
      wpasGadget.handleClose(e);
      wpasGadget.createForm();
    });

    this.formMessages.appendChild(message);
    this.formMessages.appendChild(this.createElement('p').appendChild(button));

    this.form.appendChild(this.formMessages);
    this.formWrap.appendChild(this.form);
  },

  /**
   * Get the stylesheet
   */
  getStylesheet: function () {
    var head = document.getElementsByTagName('head')[0];
    var link = document.createElement('link');
    link.id = 'awesome-support-css';
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = this.data.cssURL;
    link.media = 'all';
    head.appendChild(link);
  },

  /**
   * Get the field html
   *
   * @param field
   * @returns {*}
   */
  getField: function (field) {
    switch (field) {
      case 'password' :
        return this.createElement('input', {type: 'password', name: field, id: 'wpas_' + field});
      case 'content' :
        return this.createElement('textarea', {name: field, id: 'wpas_' + field, rows: '6'});
      case 'product' :
      case 'department' :
      case 'ticket_priority' :
        var product = this.createElement('select', {name: field, id: 'wpas_' + field});
        this.getTaxonomy(field, product);
        return product;
      case 'file' :
        return this.createElement('input', {type: 'file', name: field, id: 'wpas_' + field});
      default :
        return this.createElement('input', {type: 'text', name: field, id: 'wpas_' + field});
    }
  },

  /**
   * Create an html element
   *
   * @param type
   * @param attributes
   */
  createElement: function (type, attributes) {
    var element = document.createElement(type);

    for (var attr in attributes) {
      element.setAttribute(attr, attributes[attr]);
    }

    return element;
  },

  /**
   * Get the ticket form CSS
   */
  getCSS: function () {
    var css = '';
    css += '.wpas-button, .wpas-ticket-submit {background:' + this.data.buttonColor + ' !important;color:' + this.data.buttonTextColor + ' !important;}';
    css += '.wpas-button {' + this.data.buttonPosition + ':20px;}';
    css += '.wpas-button:hover {background:' + this.data.buttonColor + ' !important;color:' + this.data.buttonTextColor + ' !important;}';
    css += '.wpas-ticket-wrapper {background-color:' + this.data.formBackgroundColor + ';}';

    css += this.data.css;

    var style = document.createElement('style');
    style.type = 'text/css';

    if (style.styleSheet) {
      style.styleSheet.cssText = css;
    } else {
      style.appendChild(document.createTextNode(css));
    }

    return style;
  },
  
  /**
   * Hide help button if necessary
   */
  maybeHideHelpButton: function () {
    if ( 'on' == this.data.invisibleButton ) {
	  // Hide the button...
	  wpasGadget.button.style.visibility = 'hidden';
	}
  },

  /** API functions ********************************/

  /**
   * Get taxonomy options
   *
   * @param tax
   * @param element
   */
  getTaxonomy: function (tax, element) {
    var xhttp = new XMLHttpRequest();

    xhttp.open('GET', this.data.url + 'wpas-api/v1/' + tax);
    xhttp.setRequestHeader('Content-type', 'application/json');

    xhttp.onload = function () {
      var response = JSON.parse(xhttp.responseText);
      var option;

      for (var product in response) {
        if (!response[product].hasOwnProperty('id')) {
          continue;
        }
        option = wpasGadget.createElement('option', {value: response[product].id});
        option.innerHTML = response[product].name;
        element.appendChild(option);
      }
    };

    xhttp.send();
  },

  /**
   * Submit the ticket
   *
   * @param data
   */
  submitTicket: function (data) {
    var xhttp = new XMLHttpRequest();

    xhttp.open('POST', this.data.url + 'wpas-api/v1/tickets');
    xhttp.setRequestHeader('Content-type', 'application/json');

    xhttp.onload = function () {
      var response = JSON.parse(xhttp.responseText);
      if (201 === xhttp.status) {
        if (undefined !== data.file && data.file) {
          wpasGadget.sendFile(data, response);
        } else {
          wpasGadget.handleFormSuccess(response);
        }
      } else if (undefined !== response.code) {
        wpasGadget.handleFormErrors([response.message]);
      }
    };

    xhttp.send(JSON.stringify(data));
    var processing = this.createElement('p', {class: 'wpas-processing'});
    processing.innerHTML = this.data.processingText;
    this.formMessages.appendChild(processing);
  },

  /**
   * Send file attached to the form
   *
   * @param data
   * @param response
   * @returns {*}
   */
  sendFile: function (data, response) {
    if (!data.file || !response.id) {
      return wpasGadget.handleFormSuccess(response);
    }

    var formData = new FormData();
    formData.append('email', data.email);
    formData.append('file', data.file);
    formData.append('gadget_id', data.gadget_id);
    formData.append('post', response.id);

    if (undefined !== data.password) {
      formData.append('password', data.password);
    }

    var xhttp = new XMLHttpRequest();

    xhttp.open('POST', this.data.url + 'wpas-api/v1/attachments');

    xhttp.onload = function () {
      wpasGadget.handleFormSuccess(response);
    };

    xhttp.send(formData);

    var processing = this.createElement('p', {class: 'wpas-processing'});
    processing.innerHTML = this.data.uploadingText;
    this.formMessages.appendChild(processing);
  }

};

wpasGadget.init(wpasData);