(function ($) {
    if (!$) {
        return;
    }

    function extractEmoji(detail) {
        if (!detail) {
            return "";
        }
        return detail.emoji || detail.unicode || detail.native || detail.symbol || detail.char || "";
    }

    function createEmojiPicker(onSelect) {
        var picker = document.createElement("emoji-picker");
        var handler = function (event) {
            var emoji = extractEmoji(event && event.detail ? event.detail : event);
            if (!emoji) {
                return;
            }
            onSelect(emoji, event);
        };
        picker.addEventListener("emoji-click", handler);
        picker.addEventListener("emoji-select", handler);
        return picker;
    }

    function buildPickerFallback(onSelect) {
        var fallback = document.createElement("div");
        fallback.className = "emoji-picker-fallback";
        ["😀", "😂", "😍", "😎", "👍", "🎉", "🔥", "🙏", "💯", "✅"].forEach(function (emoji) {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "emoji-picker-fallback-item";
            button.textContent = emoji;
            button.addEventListener("click", function (event) {
                onSelect(emoji, event);
            });
            fallback.appendChild(button);
        });
        return fallback;
    }

    function createEditorValue($editor) {
        return $editor.text();
    }

    function setEditorValue($editor, text) {
        $editor.text(text || "");
    }

    function insertAtCursor(editor, text) {
        var selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            editor.appendChild(document.createTextNode(text));
            return;
        }

        var range = selection.getRangeAt(0);
        var editorNode = editor;
        if (!editorNode.contains(range.commonAncestorContainer)) {
            editorNode.appendChild(document.createTextNode(text));
            return;
        }
        range.deleteContents();
        range.insertNode(document.createTextNode(text));
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);
    }

    function EmojiArea($textarea, options) {
        this.$textarea = $textarea;
        this.options = options || {};
        this.$container = null;
        this.$editor = null;
        this.$button = null;
        this.$picker = null;
        this.$events = $({});
        this.disabled = false;
        this.init();
    }

    EmojiArea.prototype.init = function () {
        var self = this;
        var placeholder = self.$textarea.attr("placeholder") || "";

        var $container = $('<div class="emojionearea input-emoji form-control"></div>');
        var $editor = $('<div class="emojionearea-editor" contenteditable="true"></div>');
        var $button = $('<div class="emojionearea-button" title="Emoji"><div class="emojionearea-button-open"></div><div class="emojionearea-button-close"></div></div>');
        var $picker = $('<div class="emojionearea-picker hidden"></div>');
        var $pickerWrapper = $('<div class="emojionearea-wrapper"></div>');
        var $pickerContainer = $('<div class="emoji-picker-container"></div>');

        $editor.attr("placeholder", placeholder);
        setEditorValue($editor, self.$textarea.val());

        $pickerWrapper.append($pickerContainer);
        $picker.append($pickerWrapper);
        $container.append($editor, $button, $picker);
        self.$textarea.after($container);

        self.$container = $container;
        self.$editor = $editor;
        self.editor = $editor;
        self.$button = $button;
        self.$picker = $picker;

        var pickerElement = null;
        if (!window.customElements) {
            pickerElement = buildPickerFallback(function (emoji, event) {
                self.insertEmoji(emoji, event);
            });
        } else {
            pickerElement = createEmojiPicker(function (emoji, event) {
                self.insertEmoji(emoji, event);
            });
        }
        $pickerContainer.append(pickerElement);

        $editor.on("input", function (event) {
            self.syncTextarea();
            self.trigger("change", $editor, event);
        });

        $editor.on("keyup", function (event) {
            self.syncTextarea();
            self.trigger("keyup", $editor, event);
        });

        $button.on("click", function (event) {
            if (self.disabled) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            self.togglePicker();
            self.trigger("emojibtn.click", $button, event);
        });

        $(document).on("click", function (event) {
            if (!self.$container) {
                return;
            }
            if (self.$container.has(event.target).length === 0 && !self.$container.is(event.target)) {
                self.hidePicker();
            }
        });

        var $form = self.$textarea.closest("form");
        if ($form.length) {
            $form.on("submit", function () {
                self.syncTextarea();
            });
        }

        self.$textarea.data("emojioneArea", self);
        self.$textarea[0].emojioneArea = self;
    };

    EmojiArea.prototype.syncTextarea = function () {
        this.$textarea.val(createEditorValue(this.$editor));
    };

    EmojiArea.prototype.getText = function () {
        return createEditorValue(this.$editor);
    };

    EmojiArea.prototype.setText = function (text) {
        setEditorValue(this.$editor, text);
        this.syncTextarea();
    };

    EmojiArea.prototype.enable = function () {
        this.disabled = false;
        this.$editor.attr("contenteditable", "true");
        this.$container.removeClass("emojionearea-disable");
    };

    EmojiArea.prototype.disable = function () {
        this.disabled = true;
        this.$editor.attr("contenteditable", "false");
        this.$container.addClass("emojionearea-disable");
        this.hidePicker();
    };

    EmojiArea.prototype.togglePicker = function () {
        if (this.$picker.hasClass("hidden")) {
            this.showPicker();
        } else {
            this.hidePicker();
        }
    };

    EmojiArea.prototype.showPicker = function () {
        if (this.disabled) {
            return;
        }
        this.$picker.removeClass("hidden");
        this.$button.addClass("active");
    };

    EmojiArea.prototype.hidePicker = function () {
        this.$picker.addClass("hidden");
        this.$button.removeClass("active");
    };

    EmojiArea.prototype.insertEmoji = function (emoji, event) {
        this.$editor.focus();
        insertAtCursor(this.$editor[0], emoji);
        this.syncTextarea();
        this.trigger("change", this.$editor, event);
        this.trigger("emojibtn.click", this.$button, event);
    };

    EmojiArea.prototype.on = function (eventName, callback) {
        this.$events.on(eventName, function (event, arg1, arg2) {
            callback(arg1, arg2);
        });
    };

    EmojiArea.prototype.trigger = function (eventName, arg1, arg2) {
        this.$events.trigger(eventName, [arg1, arg2]);
    };

    $.fn.emojioneArea = function (options) {
        return this.each(function () {
            var $textarea = $(this);
            if ($textarea.data("emojioneArea")) {
                return;
            }
            new EmojiArea($textarea, options);
        });
    };
})(window.jQuery);
