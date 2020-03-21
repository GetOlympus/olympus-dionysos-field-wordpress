/*!
 * link.js v3.0.0
 * https://github.com/GetOlympus/olympus-dionysos-field-link
 *
 * This plugin adds a Link block thanks to the wpLink WordPress JS bundle.
 *
 * Example of JS:
 *      $('.wordpress').dionysosWordpress({
 *          color: '#ffaaaa',               // background color used when deleting a social network
 *          elements: '.listbox',           // node elements
 *          item: 'fieldset',               // child node item
 *          multiple: false,                // define to display multiple elements or not
 *          addbutton: '.add-button',       // node element which is used to add a new item
 *          editbutton: '.edit-button',     // node element which is used to edit item
 *          removebutton: '.remove-button', // node element which is used to remove item
 *          linkurl: '.link-url',           // node element which contains link element
 *          linklabel: '.link-label',       // node element which contains link label
 *          modal: 'find-posts',            // ---
 *          overlay: '.ui-find-overlay',    // ---
 *          source: 'template-id',          // node script element in DOM containing handlebars JS temlpate
 *      });
 *
 * Example of HTML:
 *      <div class="wordpress">
 *          <input type="hidden" name="ctm" value="" />
 *
 *          <div class="listbox">
 *              <fieldset>
 *                  <input type="hidden" name="ctm" value="123456" />
 *
 *                  <span class="link-label">My title link</span>
 *                  <a href="https://mysite.ext/slug/" class="link-url" target="_blank">https://mysite.ext/slug/</a>
 *
 *                  <a href="#" class="edit-button">Edit</a>
 *                  <a href="#" class="remove-button">Remove</a>
 *              </fieldset>
 *          </div>
 *
 *          <div class="hide-if-no-js">
 *              <a href="#" class="add-button">Add item</a>
 *          </div>
 *      </div>
 *
 *      <script type="text/html" id="tmpl-template-id">
 *          <fieldset>
 *              <input type="hidden" name="{{ fieldname }}" value="" />
 *
 *              <span class="link-label">Click on the Edit button</span>
 *              <a href="" class="link-url" target="_blank"></a>
 *
 *              <a href="#" class="edit-button">Edit</a>
 *              <a href="#" class="remove-button">Remove</a>
 *          </fieldset>
 *      </script>
 *
 *      <div id="find-posts-modal" class="find-box" style="display: none;">
 *          <div class="find-box-head">
 *              Choose a content
 *
 *              <button type="button" class="find-posts-close" id="find-posts-close">
 *                  <span class="screen-reader-text">Close</span>
 *              </button>
 *          </div>
 *
 *          <div class="find-box-inside">
 *              <div class="find-box-search">
 *                  <label class="screen-reader-text">Search</label>
 *                  <input type="text" name="search" value="" class="find-posts-input" />
 *                  <span class="find-posts-spinner spinner"></span>
 *                  <input type="button" value="Search" class="button find-posts-search" />
 *              </div>
 *
 *              <div class="find-posts-response"></div>
 *          </div>
 *
 *          <div class="find-box-buttons">
 *              <input type="submit" class="button button-primary alignright find-posts-submit" value="Submit" />
 *          </div>
 *      </div>
 *
 *      <div class="ui-find-overlay" style="display: none;"></div>
 *
 * Copyright 2016 Achraf Chouk
 * Achraf Chouk (https://github.com/crewstyle)
 */

(function ($, wp){
    "use strict";

    /**
     * Constructor
     * @param {nodeElement} $el
     * @param {array}       options
     */
    var Wordpress = function ($el,options){
        // vars
        var _this = this;

        // this plugin works ONLY with WordPress wpTemplate functions
        if (!wp || !wp.template) {
            return;
        }

        _this.$el = $el;
        _this.options = options;

        // update elements list
        _this.$elements = _this.$el.find(_this.options.elements);

        // update add button
        _this.$addbutton = _this.$el.find(_this.options.addbutton);
        _this.$submitbox = _this.$addbutton.parent();

        // update modal and overlay
        _this.$modal = _this.$el.find('#'+_this.options.modal+'-modal');
        _this.$overlay = _this.$el.find(_this.options.overlay);

        // bind click event
        _this.$addbutton.on('click', $.proxy(_this.add_block, _this));
        _this.$elements.find(_this.options.editbutton).on('click', $.proxy(_this.edit_block, _this));
        _this.$elements.find(_this.options.removebutton).on('click', $.proxy(_this.remove_block, _this));

        // update buttons
        _this.update_buttons();
    };

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$addbutton = null;

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$current = null;

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$el = null;

    /**
     * @type {array}
     */
    Wordpress.prototype.$elements = null;

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$modal = null;

    /**
     * @type {array}
     */
    Wordpress.prototype.options = null;

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$overlay = null;

    /**
     * @type {nodeElement}
     */
    Wordpress.prototype.$submitbox = null;

    /**
     * Creates a new block element in the items list, based on source template
     * @param {event} e
     */
    Wordpress.prototype.add_block = function (e){
        e.preventDefault();
        var _this = this;

        // create content from template and append to container
        var _template = wp.template(_this.options.source);
        var $html = $(_template({
            id: ''
        }));

        // bind events and append
        $html.find(_this.options.editbutton).on('click', $.proxy(_this.edit_block, _this));
        $html.find(_this.options.removebutton).on('click', $.proxy(_this.remove_block, _this));
        _this.$elements.append($html);

        // update buttons
        _this.update_buttons();
    };

    /**
     * Edits an item block contents
     * @param {event} e
     */
    Wordpress.prototype.edit_block = function (e){
        e.preventDefault();
        var _this = this;

        // vars
        var $self = $(e.target || e.currentTarget),
            $parent = $self.closest(_this.options.item);

        // set current input to update
        _this.$current = $parent;

        // init modal
        if (!_this.$modal.hasClass('initialized')) {
            _this._modal_init();
        }

        _this._modal_open();
    };

    /**
     * Removes an item block contents
     * @param {event} e
     */
    Wordpress.prototype.remove_block = function (e){
        e.preventDefault();
        var _this = this;

        // vars
        var $self = $(e.target || e.currentTarget),
            $parent = $self.closest(_this.options.item);

        // deleting animation
        $parent.css('background', _this.options.color);
        $parent.animate({
            opacity: '0'
        }, 'slow', function (){
            // remove parent and update buttons
            $parent.remove();
            _this.update_buttons();
        });
    };

    /**
     * Displays or hides interactive buttons
     */
    Wordpress.prototype.update_buttons = function (){
        var _this = this,
            _count = _this.$elements.find(_this.options.item).length;

        // single case
        if (1 <= _count && !_this.options.multiple) {
            _this.$submitbox.hide();
        }

        // other cases
        if (!_count || _this.options.multiple) {
            _this.$submitbox.show();
        }
    };

    /**
     * Close modal
     */
    Wordpress.prototype._modal_close = function (){
        var _this = this;

        _this.$modal.find('.'+_this.options.modal+'-response').empty();

        _this.$modal.hide();
        _this.$overlay.hide();
    };

    /**
     * Initialize modal with events
     */
    Wordpress.prototype._modal_init = function (){
        var _this = this;

        _this.$overlay.on('click', $.proxy(_this._modal_close, _this));

        // close modal event
        _this.$modal.find('.'+_this.options.modal+'-input').focus().keyup(function (e){
            if (27 == e.which) {
                _this._modal_close();
            }
        });
        // submit search event with ENTER button
        _this.$modal.find('.'+_this.options.modal+'-input').keypress(function (e){
            if (13 == e.which) {
                _this._modal_send_request();
                return false;
            }
        });
        // submit search event with SUBMIT click
        _this.$modal.find('.'+_this.options.modal+'-submit').click(function (e){
            e.preventDefault();
            var $checked = _this.$modal.find('.'+_this.options.modal+'-response input[type="radio"]:checked');

            if ($checked.length) {
                var _val = $checked.val(),
                    _url = $checked.closest('tr').find('td.title').attr('data-l'),
                    _txt = $checked.closest('tr').find('td.title').text();

                // update values
                _this.$current.find('input[type="hidden"]').val(_val);
                _this.$current.find(_this.options.linkurl).attr('href', _url);
                _this.$current.find(_this.options.linkurl).text(_url);
                _this.$current.find(_this.options.linklabel).text(_txt);

                // close modal
                _this._modal_close();
            }
        });

        // bind events
        _this.$modal.find('.'+_this.options.modal+'-search').on('click', $.proxy(_this._modal_send_request, _this));
        _this.$modal.find('.'+_this.options.modal+'-close').on('click', $.proxy(_this._modal_close, _this));

        // add final class
        _this.$modal.addClass('initialized');
    };

    /**
     * Open modal
     */
    Wordpress.prototype._modal_open = function (){
        var _this = this;

        _this.$overlay.show();
        _this.$modal.show();
        _this._modal_send_request();
    };

    /**
     * Send AJAX request
     */
    Wordpress.prototype._modal_send_request = function (){
        var _this = this;

        // build post object
        var post = {
            search: _this.$modal.find('.'+_this.options.modal+'-input').val(),
            type: 'post',
            action: _this.options.modal
        };

        // enable spinner
        var $spinner = _this.$modal.find('.'+_this.options.modal+'-spinner');
        $spinner.addClass('is-active');

        // response
        var $response = _this.$modal.find('.'+_this.options.modal+'-response');

        // make the AJAX request
        $.ajax(ajaxurl, {
            type: 'POST',
            data: post,
            dataType: 'json'
        }).always(function (){
            $spinner.removeClass('is-active');
        }).done(function (x){
            // no results
            if (!x.success) {
                $response.text(attachMediaBoxL10n.error);
            }

            // display data
            $response.html(x.data);
        }).fail(function (){
            $response.text(attachMediaBoxL10n.error);
        });
    };

    var methods = {
        init: function (options){
            if (!this.length) {
                return false;
            }

            var settings = {
                // configurations
                color: '#ffaaaa',
                elements: '.listbox',
                item: 'fieldset',
                multiple: false,
                // buttons
                addbutton: '.add-button',
                editbutton: '.edit-button',
                removebutton: '.remove-button',
                // link
                linkurl: '.link-url',
                linklabel: '.link-label',
                // sources
                modal: 'find-posts',
                overlay: '.ui-find-overlay',
                source: 'template-id',
            };

            return this.each(function (){
                if (options) {
                    $.extend(settings, options);
                }

                new Wordpress($(this), settings);
            });
        }
    };

    $.fn.dionysosWordpress = function (method){
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        }
        else {
            $.error('Method '+method+' does not exist on dionysosWordpress');
            return false;
        }
    };
})(window.jQuery, window.wp);
