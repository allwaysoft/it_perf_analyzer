Ext.toArray = function(a, i, j){
            return Array.prototype.slice.call(a, i || 0, j || a.length);
};

Ext.isNumber = function(v){return typeof v === 'number' && isFinite(v);};

/**
 * @class Ext.util.Format
 * Reusable data formatting functions
 * @singleton
 */
Ext.util.Format = function(){
    var trimRe = /^\s+|\s+$/g;
    return {
        /**
         * Truncate a string and add an ellipsis ('...') to the end if it exceeds the specified length
         * @param {String} value The string to truncate
         * @param {Number} length The maximum length to allow before truncating
         * @return {String} The converted text
         */
        ellipsis : function(value, len){
            if(value && value.length > len){
                return value.substr(0, len-3)+"...";
            }
            return value;
        },

        /**
         * Checks a reference and converts it to empty string if it is undefined
         * @param {Mixed} value Reference to check
         * @return {Mixed} Empty string if converted, otherwise the original value
         */
        undef : function(value){
            return typeof value != "undefined" ? value : "";
        },

        /**
         * Convert certain characters (&, <, >, and ') to their HTML character equivalents for literal display in web pages.
         * @param {String} value The string to encode
         * @return {String} The encoded text
         */
        htmlEncode : function(value){
            return !value ? value : String(value).replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
        },

        /**
         * Convert certain characters (&, <, >, and ') from their HTML character equivalents.
         * @param {String} value The string to decode
         * @return {String} The decoded text
         */
        htmlDecode : function(value){
            return !value ? value : String(value).replace(/&amp;/g, "&").replace(/&gt;/g, ">").replace(/&lt;/g, "<").replace(/&quot;/g, '"');
        },

        /**
         * Trims any whitespace from either side of a string
         * @param {String} value The text to trim
         * @return {String} The trimmed text
         */
        trim : function(value){
            return String(value).replace(trimRe, "");
        },

        /**
         * Returns a substring from within an original string
         * @param {String} value The original text
         * @param {Number} start The start index of the substring
         * @param {Number} length The length of the substring
         * @return {String} The substring
         */
        substr : function(value, start, length){
            return String(value).substr(start, length);
        },

        /**
         * Converts a string to all lower case letters
         * @param {String} value The text to convert
         * @return {String} The converted text
         */
        lowercase : function(value){
            return String(value).toLowerCase();
        },

        /**
         * Converts a string to all upper case letters
         * @param {String} value The text to convert
         * @return {String} The converted text
         */
        uppercase : function(value){
            return String(value).toUpperCase();
        },

        /**
         * Converts the first character only of a string to upper case
         * @param {String} value The text to convert
         * @return {String} The converted text
         */
        capitalize : function(value){
            return !value ? value : value.charAt(0).toUpperCase() + value.substr(1).toLowerCase();
        },

        // private
        call : function(value, fn){
            if(arguments.length > 2){
                var args = Array.prototype.slice.call(arguments, 2);
                args.unshift(value);
                return eval(fn).apply(window, args);
            }else{
                return eval(fn).call(window, value);
            }
        },

        /**
         * Format a number as US currency
         * @param {Number/String} value The numeric value to format
         * @return {String} The formatted currency string
         */
        usMoney : function(v){
            v = (Math.round((v-0)*100))/100;
            v = (v == Math.floor(v)) ? v + ".00" : ((v*10 == Math.floor(v*10)) ? v + "0" : v);
            v = String(v);
            var ps = v.split('.');
            var whole = ps[0];
            var sub = ps[1] ? '.'+ ps[1] : '.00';
            var r = /(\d+)(\d{3})/;
            while (r.test(whole)) {
                whole = whole.replace(r, '$1' + ',' + '$2');
            }
            return "$" + whole + sub ;
        },

        /**
         * Parse a value into a formatted date using the specified format pattern.
         * @param {Mixed} value The value to format
         * @param {String} format (optional) Any valid date format string (defaults to 'm/d/Y')
         * @return {String} The formatted date string
         */
        date : function(v, format){
            if(!v){
                return "";
            }
            if(!(v instanceof Date)){
                v = new Date(Date.parse(v));
            }
            return v.dateFormat(format || "m/d/Y");
        },

        /**
         * Returns a date rendering function that can be reused to apply a date format multiple times efficiently
         * @param {String} format Any valid date format string
         * @return {Function} The date formatting function
         */
        dateRenderer : function(format){
            return function(v){
                return Ext.util.Format.date(v, format);
            };
        },

        round : function(value, precision) {
            var result = Number(value);
            if (typeof precision == 'number') {
                precision = Math.pow(10, precision);
                result = Math.round(value * precision) / precision;
            }
            return result;
        },

        // private
        stripTagsRE : /<\/?[^>]+>/gi,

        /**
         * Strips all HTML tags
         * @param {Mixed} value The text from which to strip tags
         * @return {String} The stripped text
         */
        stripTags : function(v){
            return !v ? v : String(v).replace(this.stripTagsRE, "");
        }
    };
}();

Ext.isDefined = function(v){
    return typeof v !== 'undefined';
};

/*!
 * Ext JS Library 3.2.2
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
/**
 * @class Ext.form.SliderField
 * @extends Ext.form.Field
 * Wraps a {@link Ext.Slider Slider} so it can be used as a form field.
 * @constructor
 * Creates a new SliderField
 * @param {Object} config Configuration options. Note that you can pass in any slider configuration options, as well as
 * as any field configuration options.
 * @xtype sliderfield
 */
Ext.copyTo = function(dest, source, names, usePrototypeKeys){
    if(typeof names == 'string'){
        names = names.split(/[,;\s]/);
    }
    Ext.each(names, function(name){
        if(usePrototypeKeys || source.hasOwnProperty(name)){
            dest[name] = source[name];
        }
    }, this);
    return dest;
};

/*!
 * Ext JS Library 3.2.2
 * Copyright(c) 2006-2010 Ext JS, Inc.
 * licensing@extjs.com
 * http://www.extjs.com/license
 */
Ext.ns('Ext.slider');

/**
 * @class Ext.slider.Thumb
 * @extends Object
 * Represents a single thumb element on a Slider. This would not usually be created manually and would instead
 * be created internally by an {@link Ext.slider.MultiSlider Ext.Slider}.
 */
Ext.slider.Thumb = Ext.extend(Object, {


    /**
     * @constructor
     * @cfg {Ext.slider.MultiSlider} slider The Slider to render to (required)
     */
    constructor: function(config) {

        /**
         * @property slider
         * @type Ext.slider.MultiSlider
         * The slider this thumb is contained within
         */
        Ext.apply(this, config || {}, {
            cls: 'x-slider-thumb',


            /**
             * @cfg {Boolean} constrain True to constrain the thumb so that it cannot overlap its siblings
             */
            constrain: false
        });

        Ext.slider.Thumb.superclass.constructor.call(this, config);

        if (this.slider.vertical) {
            Ext.apply(this, Ext.slider.Thumb.Vertical);
        }
    },


    /**
     * Renders the thumb into a slider
     */
    render: function() {
        this.el = this.slider.innerEl.insertFirst({cls: this.cls});

        this.initEvents();
    },


    /**
     * Enables the thumb if it is currently disabled
     */
    enable: function() {
        this.disabled = false;
        this.el.removeClass(this.slider.disabledClass);
    },


    /**
     * Disables the thumb if it is currently enabled
     */
    disable: function() {
        this.disabled = true;
        this.el.addClass(this.slider.disabledClass);
    },


    /**
     * Sets up an Ext.dd.DragTracker for this thumb
     */
    initEvents: function() {
        var el = this.el;

        el.addClassOnOver('x-slider-thumb-over');

        this.tracker = new Ext.dd.DragTracker({
            onBeforeStart: this.onBeforeDragStart.createDelegate(this),
            onStart      : this.onDragStart.createDelegate(this),
            onDrag       : this.onDrag.createDelegate(this),
            onEnd        : this.onDragEnd.createDelegate(this),
            tolerance    : 3,
            autoStart    : 300
        });

        this.tracker.initEl(el);
    },

    /**
     * @private
     * This is tied into the internal Ext.dd.DragTracker. If the slider is currently disabled,
     * this returns false to disable the DragTracker too.
     * @return {Boolean} False if the slider is currently disabled
     */
    onBeforeDragStart : function(e) {
        if (this.disabled) {
            return false;
        } else {
            this.slider.promoteThumb(this);
            return true;
        }
    },

    /**
     * @private
     * This is tied into the internal Ext.dd.DragTracker's onStart template method. Adds the drag CSS class
     * to the thumb and fires the 'dragstart' event
     */
    onDragStart: function(e){
        this.el.addClass('x-slider-thumb-drag');
        this.dragging = true;
        this.dragStartValue = this.value;

        this.slider.fireEvent('dragstart', this.slider, e, this);
    },

    /**
     * @private
     * This is tied into the internal Ext.dd.DragTracker's onDrag template method. This is called every time
     * the DragTracker detects a drag movement. It updates the Slider's value using the position of the drag
     */
    onDrag: function(e) {
        var slider   = this.slider,
            index    = this.index,
            newValue = this.getNewValue();

        if (this.constrain) {
            var above = slider.thumbs[index + 1],
                below = slider.thumbs[index - 1];

            if (below != undefined && newValue <= below.value) newValue = below.value;
            if (above != undefined && newValue >= above.value) newValue = above.value;
        }

        slider.setValue(index, newValue, false);
        slider.fireEvent('drag', slider, e, this);
    },

    getNewValue: function() {
        var slider   = this.slider,
            pos      = slider.innerEl.translatePoints(this.tracker.getXY());

        return Ext.util.Format.round(slider.reverseValue(pos.left), slider.decimalPrecision);
    },

    /**
     * @private
     * This is tied to the internal Ext.dd.DragTracker's onEnd template method. Removes the drag CSS class and
     * fires the 'changecomplete' event with the new value
     */
    onDragEnd: function(e) {
        var slider = this.slider,
            value  = this.value;

        this.el.removeClass('x-slider-thumb-drag');

        this.dragging = false;
        slider.fireEvent('dragend', slider, e);

        if (this.dragStartValue != value) {
            slider.fireEvent('changecomplete', slider, value, this);
        }
    },

    /**
     * @private
     * Destroys the thumb
     */
    destroy: function(){
        Ext.destroyMembers(this, 'tracker', 'el');
    }
});

Ext.slider.Tip = Ext.extend(Ext.Tip, {
    minWidth: 10,
    offsets : [0, -10],

    init: function(slider) {
        slider.on({
            scope    : this,
            dragstart: this.onSlide,
            drag     : this.onSlide,
            dragend  : this.hide,
            destroy  : this.destroy
        });
    },

    /**
     * @private
     * Called whenever a dragstart or drag event is received on the associated Thumb.
     * Aligns the Tip with the Thumb's new position.
     * @param {Ext.slider.MultiSlider} slider The slider
     * @param {Ext.EventObject} e The Event object
     * @param {Ext.slider.Thumb} thumb The thumb that the Tip is attached to
     */
    onSlide : function(slider, e, thumb) {
        this.show();
        this.body.update(this.getText(thumb));
        this.doAutoWidth();
        this.el.alignTo(thumb.el, 'b-t?', this.offsets);
    },


    /**
     * Used to create the text that appears in the Tip's body. By default this just returns
     * the value of the Slider Thumb that the Tip is attached to. Override to customize.
     * @param {Ext.slider.Thumb} thumb The Thumb that the Tip is attached to
     * @return {String} The text to display in the tip
     */
    getText : function(thumb) {
        return String(thumb.value);
    }
});

//backwards compatibility - SliderTip used to be a ux before 3.2
Ext.ux.SliderTip = Ext.slider.Tip;

/**
 * @class Ext.slider.MultiSlider
 * @extends Ext.BoxComponent
 * Slider which supports vertical or horizontal orientation, keyboard adjustments, configurable snapping, axis clicking and animation. Can be added as an item to any container. Example usage:
 new Ext.Slider({
    renderTo: Ext.getBody(),
    width: 200,
    value: 50,
    increment: 10,
    minValue: 0,
    maxValue: 100
});

 * Sliders can be created with more than one thumb handle by passing an array of values instead of a single one:
 new Ext.Slider({
    renderTo: Ext.getBody(),
    width: 200,
    values: [25, 50, 75],
    minValue: 0,
    maxValue: 100,

    //this defaults to true, setting to false allows the thumbs to pass each other
    {@link #constrainThumbs}: false
 });

 */
Ext.slider.MultiSlider = Ext.extend(Ext.BoxComponent, {

    /**
     * @cfg {Number} value The value to initialize the slider with. Defaults to minValue.
     */

    /**
     * @cfg {Boolean} vertical Orient the Slider vertically rather than horizontally, defaults to false.
     */
    vertical: false,

    /**
     * @cfg {Number} minValue The minimum value for the Slider. Defaults to 0.
     */
    minValue: 0,

    /**
     * @cfg {Number} maxValue The maximum value for the Slider. Defaults to 100.
     */
    maxValue: 100,

    /**
     * @cfg {Number/Boolean} decimalPrecision.
     *
     The number of decimal places to which to round the Slider's value. Defaults to 0.


     *
     To disable rounding, configure as false.


     */
    decimalPrecision: 0,

    /**
     * @cfg {Number} keyIncrement How many units to change the Slider when adjusting with keyboard navigation. Defaults to 1. If the increment config is larger, it will be used instead.
     */
    keyIncrement: 1,

    /**
     * @cfg {Number} increment How many units to change the slider when adjusting by drag and drop. Use this option to enable 'snapping'.
     */
    increment: 0,

    /**
     * @private
     * @property clickRange
     * @type Array
     * Determines whether or not a click to the slider component is considered to be a user request to change the value. Specified as an array of [top, bottom],
     * the click event's 'top' property is compared to these numbers and the click only considered a change request if it falls within them. e.g. if the 'top'
     * value of the click event is 4 or 16, the click is not considered a change request as it falls outside of the [5, 15] range
     */
    clickRange: [5,15],


    /**
     * @cfg {Boolean} clickToChange Determines whether or not clicking on the Slider axis will change the slider. Defaults to true
     */
    clickToChange : true,

    /**
     * @cfg {Boolean} animate Turn on or off animation. Defaults to true
     */
    animate: true,


    /**
     * True while the thumb is in a drag operation
     * @type Boolean
     */
    dragging: false,


    /**
     * @cfg {Boolean} constrainThumbs True to disallow thumbs from overlapping one another. Defaults to true
     */
    constrainThumbs: true,

    /**
     * @private
     * @property topThumbZIndex
     * @type Number
     * The number used internally to set the z index of the top thumb (see promoteThumb for details)
     */
    topThumbZIndex: 10000,

    // private override
    initComponent : function(){
        if(!Ext.isDefined(this.value)){
            this.value = this.minValue;
        }


        /**
         * @property thumbs
         * @type Array
         * Array containing references to each thumb
         */
        this.thumbs = [];

        Ext.slider.MultiSlider.superclass.initComponent.call(this);

        this.keyIncrement = Math.max(this.increment, this.keyIncrement);
        this.addEvents(

            /**
             * @event beforechange
             * Fires before the slider value is changed. By returning false from an event handler,
             * you can cancel the event and prevent the slider from changing.
             * @param {Ext.Slider} slider The slider
             * @param {Number} newValue The new value which the slider is being changed to.
             * @param {Number} oldValue The old value which the slider was previously.
             */
            'beforechange',


            /**
             * @event change
             * Fires when the slider value is changed.
             * @param {Ext.Slider} slider The slider
             * @param {Number} newValue The new value which the slider has been changed to.
             * @param {Ext.slider.Thumb} thumb The thumb that was changed
             */
            'change',


            /**
             * @event changecomplete
             * Fires when the slider value is changed by the user and any drag operations have completed.
             * @param {Ext.Slider} slider The slider
             * @param {Number} newValue The new value which the slider has been changed to.
             * @param {Ext.slider.Thumb} thumb The thumb that was changed
             */
            'changecomplete',


            /**
             * @event dragstart
             * Fires after a drag operation has started.
             * @param {Ext.Slider} slider The slider
             * @param {Ext.EventObject} e The event fired from Ext.dd.DragTracker
             */
            'dragstart',


            /**
             * @event drag
             * Fires continuously during the drag operation while the mouse is moving.
             * @param {Ext.Slider} slider The slider
             * @param {Ext.EventObject} e The event fired from Ext.dd.DragTracker
             */
            'drag',


            /**
             * @event dragend
             * Fires after the drag operation has completed.
             * @param {Ext.Slider} slider The slider
             * @param {Ext.EventObject} e The event fired from Ext.dd.DragTracker
             */
            'dragend'
        );


        /**
         * @property values
         * @type Array
         * Array of values to initalize the thumbs with
         */
        if (this.values == undefined || Ext.isEmpty(this.values)) this.values = [0];

        var values = this.values;

        for (var i=0; i < values.length; i++) {
            this.addThumb(values[i]);
        }

        if(this.vertical){
            Ext.apply(this, Ext.slider.Vertical);
        }
    },


    /**
     * Creates a new thumb and adds it to the slider
     * @param {Number} value The initial value to set on the thumb. Defaults to 0
     */
    addThumb: function(value) {
        var thumb = new Ext.slider.Thumb({
            value    : value,
            slider   : this,
            index    : this.thumbs.length,
            constrain: this.constrainThumbs
        });
        this.thumbs.push(thumb);

        //render the thumb now if needed
        if (this.rendered) thumb.render();
    },

    /**
     * @private
     * Moves the given thumb above all other by increasing its z-index. This is called when as drag
     * any thumb, so that the thumb that was just dragged is always at the highest z-index. This is
     * required when the thumbs are stacked on top of each other at one of the ends of the slider's
     * range, which can result in the user not being able to move any of them.
     * @param {Ext.slider.Thumb} topThumb The thumb to move to the top
     */
    promoteThumb: function(topThumb) {
        var thumbs = this.thumbs,
            zIndex, thumb;

        for (var i = 0, j = thumbs.length; i < j; i++) {
            thumb = thumbs[i];

            if (thumb == topThumb) {
                zIndex = this.topThumbZIndex;
            } else {
                zIndex = '';
            }

            thumb.el.setStyle('zIndex', zIndex);
        }
    },

    // private override
    onRender : function() {
        this.autoEl = {
            cls: 'x-slider ' + (this.vertical ? 'x-slider-vert' : 'x-slider-horz'),
            cn : {
                cls: 'x-slider-end',
                cn : {
                    cls:'x-slider-inner',
                    cn : [{tag:'a', cls:'x-slider-focus', href:"#", tabIndex: '-1', hidefocus:'on'}]
                }
            }
        };

        Ext.slider.MultiSlider.superclass.onRender.apply(this, arguments);

        this.endEl   = this.el.first();
        this.innerEl = this.endEl.first();
        this.focusEl = this.innerEl.child('.x-slider-focus');

        //render each thumb
        for (var i=0; i < this.thumbs.length; i++) {
            this.thumbs[i].render();
        }

        //calculate the size of half a thumb
        var thumb      = this.innerEl.child('.x-slider-thumb');
        this.halfThumb = (this.vertical ? thumb.getHeight() : thumb.getWidth()) / 2;

        this.initEvents();
    },

    /**
     * @private
     * Adds keyboard and mouse listeners on this.el. Ignores click events on the internal focus element.
     * Creates a new DragTracker which is used to control what happens when the user drags the thumb around.
     */
    initEvents : function(){
        this.mon(this.el, {
            scope    : this,
            mousedown: this.onMouseDown,
            keydown  : this.onKeyDown
        });

        this.focusEl.swallowEvent("click", true);
    },

    /**
     * @private
     * Mousedown handler for the slider. If the clickToChange is enabled and the click was not on the draggable 'thumb',
     * this calculates the new value of the slider and tells the implementation (Horizontal or Vertical) to move the thumb
     * @param {Ext.EventObject} e The click event
     */
    onMouseDown : function(e){
        if(this.disabled){
            return;
        }

        //see if the click was on any of the thumbs
        var thumbClicked = false;
        for (var i=0; i < this.thumbs.length; i++) {
            thumbClicked = thumbClicked || e.target == this.thumbs[i].el.dom;
        }

        if (this.clickToChange && !thumbClicked) {
            var local = this.innerEl.translatePoints(e.getXY());
            this.onClickChange(local);
        }
        this.focus();
    },

    /**
     * @private
     * Moves the thumb to the indicated position. Note that a Vertical implementation is provided in Ext.slider.Vertical.
     * Only changes the value if the click was within this.clickRange.
     * @param {Object} local Object containing top and left values for the click event.
     */
    onClickChange : function(local) {
        if (local.top > this.clickRange[0] && local.top < this.clickRange[1]) {
            //find the nearest thumb to the click event
            var thumb = this.getNearest(local, 'left'),
                index = thumb.index;

            this.setValue(index, Ext.util.Format.round(this.reverseValue(local.left), this.decimalPrecision), undefined, true);
        }
    },

    /**
     * @private
     * Returns the nearest thumb to a click event, along with its distance
     * @param {Object} local Object containing top and left values from a click event
     * @param {String} prop The property of local to compare on. Use 'left' for horizontal sliders, 'top' for vertical ones
     * @return {Object} The closest thumb object and its distance from the click event
     */
    getNearest: function(local, prop) {
        var localValue = prop == 'top' ? this.innerEl.getHeight() - local[prop] : local[prop],
            clickValue = this.reverseValue(localValue),
            nearestDistance = (this.maxValue - this.minValue) + 5, //add a small fudge for the end of the slider
            index = 0,
            nearest = null;

        for (var i=0; i < this.thumbs.length; i++) {
            var thumb = this.thumbs[i],
                value = thumb.value,
                dist  = Math.abs(value - clickValue);

            if (Math.abs(dist <= nearestDistance)) {
                nearest = thumb;
                index = i;
                nearestDistance = dist;
            }
        }
        return nearest;
    },

    /**
     * @private
     * Handler for any keypresses captured by the slider. If the key is UP or RIGHT, the thumb is moved along to the right
     * by this.keyIncrement. If DOWN or LEFT it is moved left. Pressing CTRL moves the slider to the end in either direction
     * @param {Ext.EventObject} e The Event object
     */
    onKeyDown : function(e){
        /*
         * The behaviour for keyboard handling with multiple thumbs is currently undefined.
         * There's no real sane default for it, so leave it like this until we come up
         * with a better way of doing it.
         */
        if(this.disabled || this.thumbs.length !== 1){
            e.preventDefault();
            return;
        }
        var k = e.getKey(),
            val;
        switch(k){
            case e.UP:
            case e.RIGHT:
                e.stopEvent();
                val = e.ctrlKey ? this.maxValue : this.getValue(0) + this.keyIncrement;
                this.setValue(0, val, undefined, true);
                break;
            case e.DOWN:
            case e.LEFT:
                e.stopEvent();
                val = e.ctrlKey ? this.minValue : this.getValue(0) - this.keyIncrement;
                this.setValue(0, val, undefined, true);
                break;
            default:
                e.preventDefault();
        }
    },

    /**
     * @private
     * If using snapping, this takes a desired new value and returns the closest snapped
     * value to it
     * @param {Number} value The unsnapped value
     * @return {Number} The value of the nearest snap target
     */
    doSnap : function(value){
        if (!(this.increment && value)) {
            return value;
        }
        var newValue = value,
            inc = this.increment,
            m = value % inc;
        if (m != 0) {
            newValue -= m;
            if (m * 2 >= inc) {
                newValue += inc;
            } else if (m * 2 < -inc) {
                newValue -= inc;
            }
        }
        return newValue.constrain(this.minValue,  this.maxValue);
    },

    // private
    afterRender : function(){
        Ext.slider.MultiSlider.superclass.afterRender.apply(this, arguments);

        for (var i=0; i < this.thumbs.length; i++) {
            var thumb = this.thumbs[i];

            if (thumb.value !== undefined) {
                var v = this.normalizeValue(thumb.value);

                if (v !== thumb.value) {
                    // delete this.value;
                    this.setValue(i, v, false);
                } else {
                    this.moveThumb(i, this.translateValue(v), false);
                }
            }
        };
    },

    /**
     * @private
     * Returns the ratio of pixels to mapped values. e.g. if the slider is 200px wide and maxValue - minValue is 100,
     * the ratio is 2
     * @return {Number} The ratio of pixels to mapped values
     */
    getRatio : function(){
        var w = this.innerEl.getWidth(),
            v = this.maxValue - this.minValue;
        return v == 0 ? w : (w/v);
    },

    /**
     * @private
     * Returns a snapped, constrained value when given a desired value
     * @param {Number} value Raw number value
     * @return {Number} The raw value rounded to the correct d.p. and constrained within the set max and min values
     */
    normalizeValue : function(v){
        v = this.doSnap(v);
        v = Ext.util.Format.round(v, this.decimalPrecision);
        v = v.constrain(this.minValue, this.maxValue);
        return v;
    },


    /**
     * Sets the minimum value for the slider instance. If the current value is less than the
     * minimum value, the current value will be changed.
     * @param {Number} val The new minimum value
     */
    setMinValue : function(val){
        this.minValue = val;
        var i = 0,
            thumbs = this.thumbs,
            len = thumbs.length,
            t;

        for(; i < len; ++i){
            t = thumbs[i];
            t.value = t.value < val ? val : t.value;
        }
        this.syncThumb();
    },


    /**
     * Sets the maximum value for the slider instance. If the current value is more than the
     * maximum value, the current value will be changed.
     * @param {Number} val The new maximum value
     */
    setMaxValue : function(val){
        this.maxValue = val;
        var i = 0,
            thumbs = this.thumbs,
            len = thumbs.length,
            t;

        for(; i < len; ++i){
            t = thumbs[i];
            t.value = t.value > val ? val : t.value;
        }
        this.syncThumb();
    },


    /**
     * Programmatically sets the value of the Slider. Ensures that the value is constrained within
     * the minValue and maxValue.
     * @param {Number} index Index of the thumb to move
     * @param {Number} value The value to set the slider to. (This will be constrained within minValue and maxValue)
     * @param {Boolean} animate Turn on or off animation, defaults to true
     */
    setValue : function(index, v, animate, changeComplete) {
        var thumb = this.thumbs[index],
            el    = thumb.el;

        v = this.normalizeValue(v);

        if (v !== thumb.value && this.fireEvent('beforechange', this, v, thumb.value, thumb) !== false) {
            thumb.value = v;
            if(this.rendered){
                this.moveThumb(index, this.translateValue(v), animate !== false);
                this.fireEvent('change', this, v, thumb);
                if(changeComplete){
                    this.fireEvent('changecomplete', this, v, thumb);
                }
            }
        }
    },

    /**
     * @private
     */
    translateValue : function(v) {
        var ratio = this.getRatio();
        return (v * ratio) - (this.minValue * ratio) - this.halfThumb;
    },

    /**
     * @private
     * Given a pixel location along the slider, returns the mapped slider value for that pixel.
     * E.g. if we have a slider 200px wide with minValue = 100 and maxValue = 500, reverseValue(50)
     * returns 200
     * @param {Number} pos The position along the slider to return a mapped value for
     * @return {Number} The mapped value for the given position
     */
    reverseValue : function(pos){
        var ratio = this.getRatio();
        return (pos + (this.minValue * ratio)) / ratio;
    },

    /**
     * @private
     * @param {Number} index Index of the thumb to move
     */
    moveThumb: function(index, v, animate){
        var thumb = this.thumbs[index].el;

        if(!animate || this.animate === false){
            thumb.setLeft(v);
        }else{
            thumb.shift({left: v, stopFx: true, duration:.35});
        }
    },

    // private
    focus : function(){
        this.focusEl.focus(10);
    },

    // private
    onResize : function(w, h){
        var thumbs = this.thumbs,
            len = thumbs.length,
            i = 0;

        /*
         * If we happen to be animating during a resize, the position of the thumb will likely be off
         * when the animation stops. As such, just stop any animations before syncing the thumbs.
         */
        for(; i < len; ++i){
            thumbs[i].el.stopFx();
        }
        // check to see if we're using an auto width
        if(Ext.isNumber(w)){
            this.innerEl.setWidth(w - (this.el.getPadding('l') + this.endEl.getPadding('r')));
        }
        this.syncThumb();
        Ext.slider.MultiSlider.superclass.onResize.apply(this, arguments);
    },

    //private
    onDisable: function(){
        Ext.slider.MultiSlider.superclass.onDisable.call(this);

        for (var i=0; i < this.thumbs.length; i++) {
            var thumb = this.thumbs[i],
                el    = thumb.el;

            thumb.disable();

            if(Ext.isIE){
                //IE breaks when using overflow visible and opacity other than 1.
                //Create a place holder for the thumb and display it.
                var xy = el.getXY();
                el.hide();

                this.innerEl.addClass(this.disabledClass).dom.disabled = true;

                if (!this.thumbHolder) {
                    this.thumbHolder = this.endEl.createChild({cls: 'x-slider-thumb ' + this.disabledClass});
                }

                this.thumbHolder.show().setXY(xy);
            }
        }
    },

    //private
    onEnable: function(){
        Ext.slider.MultiSlider.superclass.onEnable.call(this);

        for (var i=0; i < this.thumbs.length; i++) {
            var thumb = this.thumbs[i],
                el    = thumb.el;

            thumb.enable();

            if (Ext.isIE) {
                this.innerEl.removeClass(this.disabledClass).dom.disabled = false;

                if (this.thumbHolder) this.thumbHolder.hide();

                el.show();
                this.syncThumb();
            }
        }
    },


    /**
     * Synchronizes the thumb position to the proper proportion of the total component width based
     * on the current slider {@link #value}.  This will be called automatically when the Slider
     * is resized by a layout, but if it is rendered auto width, this method can be called from
     * another resize handler to sync the Slider if necessary.
     */
    syncThumb : function() {
        if (this.rendered) {
            for (var i=0; i < this.thumbs.length; i++) {
                this.moveThumb(i, this.translateValue(this.thumbs[i].value));
            }
        }
    },


    /**
     * Returns the current value of the slider
     * @param {Number} index The index of the thumb to return a value for
     * @return {Number} The current value of the slider
     */
    getValue : function(index) {
        return this.thumbs[index].value;
    },


    /**
     * Returns an array of values - one for the location of each thumb
     * @return {Array} The set of thumb values
     */
    getValues: function() {
        var values = [];

        for (var i=0; i < this.thumbs.length; i++) {
            values.push(this.thumbs[i].value);
        }

        return values;
    },

    // private
    beforeDestroy : function(){
        var thumbs = this.thumbs;
        for(var i = 0, len = thumbs.length; i < len; ++i){
            thumbs[i].destroy();
            thumbs[i] = null;
        }
        Ext.destroyMembers(this, 'endEl', 'innerEl', 'focusEl', 'thumbHolder');
        Ext.slider.MultiSlider.superclass.beforeDestroy.call(this);
    }
});

Ext.reg('multislider', Ext.slider.MultiSlider);

/**
 * @class Ext.slider.SingleSlider
 * @extends Ext.slider.MultiSlider
 * Slider which supports vertical or horizontal orientation, keyboard adjustments,
 * configurable snapping, axis clicking and animation. Can be added as an item to
 * any container. Example usage:

 new Ext.slider.SingleSlider({
    renderTo: Ext.getBody(),
    width: 200,
    value: 50,
    increment: 10,
    minValue: 0,
    maxValue: 100
});

 * The class Ext.slider.SingleSlider is aliased to Ext.Slider for backwards compatibility.
 */
Ext.slider.SingleSlider = Ext.extend(Ext.slider.MultiSlider, {
    constructor: function(config) {
        config = config || {};

        Ext.applyIf(config, {
            values: [config.value || 0]
        });

        Ext.slider.SingleSlider.superclass.constructor.call(this, config);
    },


    /**
     * Returns the current value of the slider
     * @return {Number} The current value of the slider
     */
    getValue: function() {
        //just returns the value of the first thumb, which should be the only one in a single slider
        return Ext.slider.SingleSlider.superclass.getValue.call(this, 0);
    },


    /**
     * Programmatically sets the value of the Slider. Ensures that the value is constrained within
     * the minValue and maxValue.
     * @param {Number} value The value to set the slider to. (This will be constrained within minValue and maxValue)
     * @param {Boolean} animate Turn on or off animation, defaults to true
     */
    setValue: function(value, animate) {
        var args = Ext.toArray(arguments),
            len  = args.length;

        //this is to maintain backwards compatiblity for sliders with only one thunb. Usually you must pass the thumb
        //index to setValue, but if we only have one thumb we inject the index here first if given the multi-slider
        //signature without the required index. The index will always be 0 for a single slider
        if (len == 1 || (len <= 3 && typeof arguments[1] != 'number')) {
            args.unshift(0);
        }

        return Ext.slider.SingleSlider.superclass.setValue.apply(this, args);
    },


    /**
     * Synchronizes the thumb position to the proper proportion of the total component width based
     * on the current slider {@link #value}.  This will be called automatically when the Slider
     * is resized by a layout, but if it is rendered auto width, this method can be called from
     * another resize handler to sync the Slider if necessary.
     */
    syncThumb : function() {
        return Ext.slider.SingleSlider.superclass.syncThumb.apply(this, [0].concat(arguments));
    },

    // private
    getNearest : function(){
        // Since there's only 1 thumb, it's always the nearest
        return this.thumbs[0];
    }
});

//backwards compatibility
Ext.Slider = Ext.slider.SingleSlider;

Ext.reg('slider', Ext.slider.SingleSlider);

// private class to support vertical sliders
Ext.slider.Vertical = {
    onResize : function(w, h){
        this.innerEl.setHeight(h - (this.el.getPadding('t') + this.endEl.getPadding('b')));
        this.syncThumb();
    },

    getRatio : function(){
        var h = this.innerEl.getHeight(),
            v = this.maxValue - this.minValue;
        return h/v;
    },

    moveThumb: function(index, v, animate) {
        var thumb = this.thumbs[index],
            el    = thumb.el;

        if (!animate || this.animate === false) {
            el.setBottom(v);
        } else {
            el.shift({bottom: v, stopFx: true, duration:.35});
        }
    },

    onClickChange : function(local) {
        if (local.left > this.clickRange[0] && local.left < this.clickRange[1]) {
            var thumb = this.getNearest(local, 'top'),
                index = thumb.index,
                value = this.minValue + this.reverseValue(this.innerEl.getHeight() - local.top);

            this.setValue(index, Ext.util.Format.round(value, this.decimalPrecision), undefined, true);
        }
    }
};

//private class to support vertical dragging of thumbs within a slider
Ext.slider.Thumb.Vertical = {
    getNewValue: function() {
        var slider   = this.slider,
            innerEl  = slider.innerEl,
            pos      = innerEl.translatePoints(this.tracker.getXY()),
            bottom   = innerEl.getHeight() - pos.top;

        return slider.minValue + Ext.util.Format.round(bottom / slider.getRatio(), slider.decimalPrecision);
    }
};

Ext.form.SliderField = Ext.extend(Ext.form.Field, {


    /**
     * @cfg {Boolean} useTips
     * True to use an Ext.slider.Tip to display tips for the value. Defaults to true.
     */
    useTips : true,


    /**
     * @cfg {Function} tipText
     * A function used to display custom text for the slider tip. Defaults to null, which will
     * use the default on the plugin.
     */
    tipText : null,

    // private override
    actionMode: 'wrap',

    /**
     * Initialize the component.
     * @private
     */
    initComponent : function() {
        var cfg = Ext.copyTo({
            id: this.id + '-slider'
        }, this.initialConfig, ['vertical', 'minValue', 'maxValue', 'decimalPrecision', 'keyIncrement', 'increment', 'clickToChange', 'animate']);

        // only can use it if it exists.
        if (this.useTips) {
            var plug = this.tipText ? {getText: this.tipText} : {};
            cfg.plugins = [new Ext.slider.Tip(plug)];
        }
        this.slider = new Ext.Slider(cfg);
        Ext.form.SliderField.superclass.initComponent.call(this);
    },

    /**
     * Set up the hidden field
     * @param {Object} ct The container to render to.
     * @param {Object} position The position in the container to render to.
     * @private
     */
    onRender : function(ct, position){
        this.autoCreate = {
            id: this.id,
            name: this.name,
            type: 'hidden',
            tag: 'input'
        };
        Ext.form.SliderField.superclass.onRender.call(this, ct, position);
        this.wrap = this.el.wrap({cls: 'x-form-field-wrap'});
        this.resizeEl = this.positionEl = this.wrap;
        this.slider.render(this.wrap);
    },

    /**
     * Ensure that the slider size is set automatically when the field resizes.
     * @param {Object} w The width
     * @param {Object} h The height
     * @param {Object} aw The adjusted width
     * @param {Object} ah The adjusted height
     * @private
     */
    onResize : function(w, h, aw, ah){
        Ext.form.SliderField.superclass.onResize.call(this, w, h, aw, ah);
        this.slider.setSize(w, h);
    },

    /**
     * Initialize any events for this class.
     * @private
     */
    initEvents : function(){
        Ext.form.SliderField.superclass.initEvents.call(this);
        this.slider.on('change', this.onChange, this);
    },

    /**
     * Utility method to set the value of the field when the slider changes.
     * @param {Object} slider The slider object.
     * @param {Object} v The new value.
     * @private
     */
    onChange : function(slider, v){
        this.setValue(v, undefined, true);
    },

    /**
     * Enable the slider when the field is enabled.
     * @private
     */
    onEnable : function(){
        Ext.form.SliderField.superclass.onEnable.call(this);
        this.slider.enable();
    },

    /**
     * Disable the slider when the field is disabled.
     * @private
     */
    onDisable : function(){
        Ext.form.SliderField.superclass.onDisable.call(this);
        this.slider.disable();
    },

    /**
     * Ensure the slider is destroyed when the field is destroyed.
     * @private
     */
    beforeDestroy : function(){
        Ext.destroy(this.slider);
        Ext.form.SliderField.superclass.beforeDestroy.call(this);
    },

    /**
     * If a side icon is shown, do alignment to the slider
     * @private
     */
    alignErrorIcon : function(){
        this.errorIcon.alignTo(this.slider.el, 'tl-tr', [2, 0]);
    },


    /**
     * Sets the minimum field value.
     * @param {Number} v The new minimum value.
     * @return {Ext.form.SliderField} this
     */
    setMinValue : function(v){
        this.slider.setMinValue(v);
        return this;
    },


    /**
     * Sets the maximum field value.
     * @param {Number} v The new maximum value.
     * @return {Ext.form.SliderField} this
     */
    setMaxValue : function(v){
        this.slider.setMaxValue(v);
        return this;
    },


    /**
     * Sets the value for this field.
     * @param {Number} v The new value.
     * @param {Boolean} animate (optional) Whether to animate the transition. If not specified, it will default to the animate config.
     * @return {Ext.form.SliderField} this
     */
    setValue : function(v, animate, /* private */ silent){
        // silent is used if the setValue method is invoked by the slider
        // which means we don't need to set the value on the slider.
        if(!silent){
            this.slider.setValue(v, animate);
        }
        return Ext.form.SliderField.superclass.setValue.call(this, this.slider.getValue());
    },


    /**
     * Gets the current value for this field.
     * @return {Number} The current value.
     */
    getValue : function(){
        return this.slider.getValue();
    }
});

Ext.reg('sliderfield', Ext.form.SliderField);