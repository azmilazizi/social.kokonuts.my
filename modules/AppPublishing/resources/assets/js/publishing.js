"use strict";

var AppPubishing = new (function () {
    var AppPubishing = this;
    var Calendar = this;

    /*
    * FULL CALENDAR
     */
    var CALENDAR_SELECTORS = {
        "TITLE": '.calendar-title',
        "HEADER": '.calendar-header',
        "MAIN": '.main',
    };

    AppPubishing.init = function (reload) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': VARIABLES.csrf
            }
        });

        if (reload || reload == undefined) {

            AppPubishing.Calendar();
            AppPubishing.CalendarTitle();
            AppPubishing.CalendarEvents();
            AppPubishing.CalendarHeight();
            AppPubishing.CalendarAction();
            AppPubishing.Actions();
        }

        if ($(".composer-scheduling").length > 0) {
            AppPubishing.previewAction();
            AppPubishing.preview();
            AppPubishing.initThumbnailDropzone();
            AppPubishing.initCaptionByNetwork();
        }

    },

        AppPubishing.Actions = function () {
            $(document).on("click", ".closeCompose", function () {
                AppPubishing.closeCompose();
            });

            $(document).on("click", ".showCompose", function () {
                $(".compose-media,.compose-preview").removeClass("active");
            });

            $(document).on("click", ".showMedia", function () {
                $(".compose-media").addClass("active");
            });

            $(document).on("click", ".showPreview", function () {
                $(".compose-preview").addClass("active");
            });

            var type = $(".compose-type input:checked").val();
            AppPubishing.composeType(type);

            $(document).on("change", ".compose-type input", function () {
                type = $(this).val();
                AppPubishing.composeType(type);
            });

            $(document).on("change", ".compose select[name='post_by']", function () {
                var that = $(this);
                var type = $(this).val();
                $(".compose .post-by").addClass("d-none");
                $(".compose .post-by[data-by='" + type + "']").removeClass("d-none").show();

                if (type == 1) {
                    $(".btnPostNow").removeClass("d-none");
                    $(".btnSchedulePost").addClass("d-none");
                    $(".btnSaveDraft").addClass("d-none");
                } else if (type == 4) {
                    $(".btnPostNow").addClass("d-none");
                    $(".btnSchedulePost").addClass("d-none");
                    $(".btnSaveDraft").removeClass("d-none");
                } else {
                    $(".btnPostNow").addClass("d-none");
                    $(".btnSchedulePost").removeClass("d-none");
                    $(".btnSaveDraft").addClass("d-none");
                }
            });

            $(document).on("click", ".compose .addSpecificDays", function () {
                var that = $(this);
                var item = $(".tempPostByDays").find(".item");
                var c = item.clone();
                c.find("input").attr("name", "time_posts[]").addClass("datetime").val("");
                $(".listPostByDays").append(c);
                Main.DateTime();

                if ($(".compose .listPostByDays .remove").length > 1) {
                    $(".compose .listPostByDays .remove").removeClass("disabled");
                }

                return false;
            });

            $(document).on("click", ".compose .listPostByDays .remove:not(.disabled)", function () {
                var that = $(this);
                that.parents(".item").remove();

                if ($(".compose .listPostByDays .remove").length < 2) {
                    $(".compose .listPostByDays .remove").addClass("disabled");
                }
            });
        },

        AppPubishing.getSelectedAccounts = function () {
            var selections = [];
            $(".am-list-account .am-choice-body .am-choice-item input:checked").each(function () {
                var $item = $(this).closest(".am-choice-item");
                if ($item.length) {
                    selections.push($item);
                }
            });

            if (selections.length === 0) {
                $(".am-selected-list .am-selected-item").each(function () {
                    var id = $(this).data("id");
                    if (!id) {
                        return;
                    }
                    var $item = $(".am-list-account .am-choice-body .am-choice-item input[value='" + id + "']").closest(".am-choice-item");
                    if ($item.length) {
                        selections.push($item);
                    }
                });
            }

            return selections;
        },

        AppPubishing.initCaptionByNetwork = function () {
            var $container = $("[data-caption-by-network]");
            if (!$container.length || $container.data("caption-network-init")) {
                return;
            }

            $container.data("caption-network-init", true);

            var $tabsWrap = $container.find("[data-caption-network-tabs]");
            var $tabList = $container.find("[data-caption-tab-list]");
            var $panels = $container.find("[data-caption-network-panels]");
            var templateLabel = $container.data("template-label") || "Template";
            var promptText = $container.data("caption-edit-prompt") || "Click this button to stop using the current template and customize the post";
            var editLabel = $container.data("caption-edit-button") || "Edit";

            function getSelectedAccountsData() {
                return AppPubishing.getSelectedAccounts().map(function ($item) {
                    var id = $item.find("input").val();
                    var name = $item.data("name") || $item.find(".text-gray-800").first().text().trim();
                    var network = $item.data("social-network") || $item.data("network");
                    return {
                        id: id,
                        name: name || id,
                        network: network || ""
                    };
                });
            }

            function buildTabButton(key, label) {
                return `
                    <button type="button" class="nav-link" data-caption-tab="${key}">
                        ${label}
                    </button>
                `;
            }

            function buildCaptionPanel(account) {
                var panel = $(`
                    <div class="caption-panel d-none" data-caption-panel="${account.id}">
                        <textarea class="form-control input-emoji post-caption-network fw-4 border" name="captions[${account.id}]" placeholder="${templateLabel}"></textarea>
                        <div class="caption-disabled-overlay" data-caption-disabled>
                            <div class="caption-disabled-content">
                                <div class="text-center mb-2">${promptText}</div>
                                <button type="button" class="btn btn-primary btn-sm" data-caption-enable>
                                    ${editLabel}
                                </button>
                            </div>
                        </div>
                    </div>
                `);

                var $textarea = panel.find("textarea");
                AppPubishing.initEmojiArea($textarea);
                AppPubishing.disableCaptionField($textarea);
                AppPubishing.bindCaptionEvents($textarea);
                return panel;
            }

            function ensureTemplatePanel() {
                var $templatePanel = $panels.find('[data-caption-panel="template"]');
                if ($templatePanel.length) {
                    AppPubishing.bindCaptionEvents($templatePanel.find("textarea"));
                }
            }

            function buildTabs() {
                var accounts = getSelectedAccountsData();
                var selectedKeys = accounts.map(function (account) {
                    return account.id;
                });

                $panels.find("[data-caption-panel]").each(function () {
                    var key = $(this).data("caption-panel");
                    if (key === "template") {
                        return;
                    }
                    if (selectedKeys.indexOf(key) === -1) {
                        $(this).remove();
                    }
                });

                accounts.forEach(function (account) {
                    if (!$panels.find('[data-caption-panel="' + account.id + '"]').length) {
                        $panels.append(buildCaptionPanel(account));
                    }
                });

                var tabsHtml = [buildTabButton("template", templateLabel)];
                accounts.forEach(function (account) {
                    tabsHtml.push(buildTabButton(account.id, account.name));
                });
                $tabList.html(tabsHtml.join(""));
                activateTab($tabList.find('[data-caption-tab="template"]'));
            }

            function activateTab($tab) {
                if (!$tab || !$tab.length) {
                    return;
                }
                var key = $tab.data("caption-tab");
                $tabList.find(".nav-link").removeClass("active");
                $tab.addClass("active");
                $panels.find("[data-caption-panel]").addClass("d-none").removeClass("is-active");
                var $panel = $panels.find('[data-caption-panel="' + key + '"]');
                $panel.removeClass("d-none").addClass("is-active");
                AppPubishing.refreshCaptionPreview();
            }

            function isNetworkModeActive() {
                return $container.hasClass("is-caption-network-active");
            }

            function updateTabsVisibility() {
                $tabsWrap.toggleClass("d-none", !isNetworkModeActive());
            }

            $(document).on("click", "[data-caption-network-toggle]", function () {
                $container.toggleClass("is-caption-network-active");
                if (isNetworkModeActive()) {
                    buildTabs();
                } else {
                    $tabList.find(".nav-link").removeClass("active");
                    $panels.find("[data-caption-panel]").addClass("d-none").removeClass("is-active");
                    $panels.find('[data-caption-panel="template"]').removeClass("d-none").addClass("is-active");
                    AppPubishing.refreshCaptionPreview();
                }
                updateTabsVisibility();
            });

            $(document).on("click", "[data-caption-tab]", function () {
                activateTab($(this));
            });

            $(document).on("click", "[data-caption-enable]", function () {
                var $panel = $(this).closest("[data-caption-panel]");
                var $textarea = $panel.find("textarea");
                var templateText = AppPubishing.getCaptionText($panels.find('[data-caption-panel="template"] textarea'));
                AppPubishing.setCaptionText($textarea, templateText);
                AppPubishing.enableCaptionField($textarea);
                $panel.find("[data-caption-disabled]").addClass("d-none");
                $panel.addClass("is-enabled");
                AppPubishing.refreshCaptionPreview();
            });

            var selectionContainer = document.querySelector(".am-selected-list");
            if (selectionContainer) {
                var selectionObserver = new MutationObserver(function () {
                    if (isNetworkModeActive()) {
                        buildTabs();
                    }
                });
                selectionObserver.observe(selectionContainer, { childList: true, subtree: false });
            }

            $(document).on("change", ".am-choice-item input[type='checkbox']", function () {
                if (isNetworkModeActive()) {
                    buildTabs();
                }
            });

            ensureTemplatePanel();
            updateTabsVisibility();
        },

        AppPubishing.initEmojiArea = function ($textarea) {
            if (!$textarea || !$textarea.length) {
                return;
            }
            if ($textarea.data("emojioneArea")) {
                return;
            }
            $textarea.emojioneArea({
                hideSource: true,
                useSprite: false,
                pickerPosition: "bottom",
                filtersPosition: "top"
            });
        },

        AppPubishing.disableCaptionField = function ($textarea) {
            if (!$textarea || !$textarea.length) {
                return;
            }
            $textarea.prop("disabled", true);
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.disable) {
                emojiArea.disable();
            }
        },

        AppPubishing.enableCaptionField = function ($textarea) {
            if (!$textarea || !$textarea.length) {
                return;
            }
            $textarea.prop("disabled", false);
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.enable) {
                emojiArea.enable();
            }
        },

        AppPubishing.getCaptionText = function ($textarea) {
            if (!$textarea || !$textarea.length) {
                return "";
            }
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.getText) {
                return emojiArea.getText();
            }
            return $textarea.val() || "";
        },

        AppPubishing.setCaptionText = function ($textarea, text) {
            if (!$textarea || !$textarea.length) {
                return;
            }
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.setText) {
                emojiArea.setText(text || "");
                return;
            }
            $textarea.val(text || "");
        },

        AppPubishing.getCaptionContent = function ($textarea, editor) {
            if (editor && editor.html) {
                return editor.html();
            }
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.editor) {
                return emojiArea.editor.html();
            }
            return $textarea.val() || "";
        },

        AppPubishing.isCaptionPanelActive = function ($textarea) {
            var $panel = $textarea.closest("[data-caption-panel]");
            if (!$panel.length) {
                return true;
            }
            if ($panel.hasClass("is-active")) {
                return true;
            }
            var $container = $panel.closest("[data-caption-by-network]");
            return !$container.hasClass("is-caption-network-active") && $panel.data("caption-panel") === "template";
        },

        AppPubishing.getActiveCaptionTextarea = function () {
            var $container = $("[data-caption-by-network]");
            var $activePanel = $container.find("[data-caption-panel].is-active");
            if ($activePanel.length) {
                var $textarea = $activePanel.find("textarea").first();
                if ($textarea.length) {
                    return $textarea;
                }
            }
            return $(".post-caption").first();
        },

        AppPubishing.refreshCaptionPreview = function () {
            var $textarea = AppPubishing.getActiveCaptionTextarea();
            if (!$textarea.length) {
                return;
            }
            var text = AppPubishing.getCaptionText($textarea);
            var content = AppPubishing.getCaptionContent($textarea);
            $textarea.closest(".wrap-input-emoji").find(".count-word span").html(text.length);
            if (text !== "") {
                $(".cpv-text").html(content);
            } else {
                $(".cpv-text").html('<div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1"></div><div class="h-12 bg-gray-200 mb-1 wp-50"></div>');
            }
        },

        AppPubishing.bindCaptionEvents = function ($textarea) {
            if (!$textarea || !$textarea.length || $textarea.data("caption-events")) {
                return;
            }
            $textarea.data("caption-events", true);
            var emojiArea = $textarea[0] && $textarea[0].emojioneArea;
            if (emojiArea && emojiArea.on) {
                emojiArea.on("keyup", function (editor, event) {
                    if (!AppPubishing.isCaptionPanelActive($textarea)) {
                        return;
                    }
                    AppPubishing.refreshCaptionPreview();
                });

                emojiArea.on("change", function (editor, event) {
                    if (!AppPubishing.isCaptionPanelActive($textarea)) {
                        return;
                    }
                    AppPubishing.refreshCaptionPreview();
                });

                emojiArea.on("emojibtn.click", function (button, event) {
                    if (!AppPubishing.isCaptionPanelActive($textarea)) {
                        return;
                    }
                    AppPubishing.refreshCaptionPreview();
                });
            } else {
                $textarea.on("input", function () {
                    if (!AppPubishing.isCaptionPanelActive($textarea)) {
                        return;
                    }
                    AppPubishing.refreshCaptionPreview();
                });
            }
        },

        AppPubishing.previewAction = function () {
            function channelChanges() {
                var selectedAccounts = AppPubishing.getSelectedAccounts();
                if (selectedAccounts.length > 0) {
                    $('.cpv-empty').addClass('d-none');
                } else {
                    $('.cpv-empty').removeClass('d-none');
                }
                AppPubishing.preview();
            }

            // Setup MutationObserver
            var container = document.querySelector('.am-selected-list');
            if (container) {
                var fb_observer = new MutationObserver(channelChanges);
                fb_observer.observe(container, {
                    childList: true,
                    subtree: false,
                    attributes: true
                });

                channelChanges();
            }

            $(document).on("change", ".am-choice-item input[type='checkbox']", function () {
                channelChanges();
            });

            if ($(".post-caption").length > 0) {
                AppPubishing.bindCaptionEvents($(".post-caption").eq(0));
            }
        },

        AppPubishing.preview = function () {
            var profileFound = false;
            $(".cpv").addClass("d-none");
            var selectedAccounts = AppPubishing.getSelectedAccounts();
            selectedAccounts.forEach(function ($item) {
                var network = ($item.data("social-network") || $item.data("network") || '').toString().toLowerCase();
                var avatar = $item.data("avatar");
                var name = $item.data("name");
                var username = $item.data("username");
                $(".cpv").each(function () {
                    var $cpv = $(this);
                    var previewNetwork = ($cpv.data("social-network") || '').toString().toLowerCase();
                    if (network && previewNetwork && network == previewNetwork) {
                        $cpv.removeClass("d-none");
                        $cpv.find(".cpv-avatar").attr("src", avatar);
                        $cpv.find(".cpv-name").text(name);
                        $cpv.find(".cpv-username").text(username);
                        profileFound = true;
                    }
                });
            });

            if (!profileFound) {
                var $profile = $('.preview-profile');
                if ($profile.length) {
                    var avatar = $profile.data('avatar');
                    var name = $profile.data('name');
                    var username = $profile.data('username');
                    var network = $profile.data('social-network');
                    $('.cpvx').each(function () {
                        var $cpv = $(this);
                        var previewNetwork = $cpv.data("social-network");
                        if (!previewNetwork || previewNetwork == network) {
                            $cpv.removeClass("d-none");
                            $cpv.find(".cpv-avatar").attr("src", avatar);
                            $cpv.find(".cpv-name").text(name);
                            $cpv.find(".cpv-username").text(username);
                        }
                    });
                }
            }

            var postType = $('[name="type"]:checked').val();
            if ($('.preview-post-type').length > 0) {
                postType = $('.preview-post-type').val();
            }
            switch (postType) {
                case "text":
                    $(".cpv-link, .cpv-media").addClass('d-none');
                    break;
                case "link":
                    $(".cpv-link").removeClass('d-none');
                    $(".cpv-media").addClass('d-none');
                    $(".compose-editor [name='link']").trigger("change");
                    break;
                default:
                    $(".cpv-media").removeClass('d-none');
                    $(".cpv-link").addClass('d-none');
                    break;
            }

            AppPubishing.refreshCaptionPreview();

            function onMediaItemsChange() {
                var images = document.querySelectorAll('.compose-type-media .file-selected-media .items .file-item');
                let allMedias;
                if (images.length > 0) {
                    allMedias = Array.from(images);
                } else {
                    var previewMedias = document.querySelectorAll('.preview-list-medias [data-preview-media]');
                    allMedias = Array.from(previewMedias);
                }
                const previewHtml = allMedias.map(media => {
                    var type = media.dataset?.type || 'image';
                    var file = media.dataset?.file || media.src;
                    if (type == "image") {
                        return `<img src="${file}"/>`;
                    } else if (type == "video") {
                        return `
                        <div class="cpv-video-frame" data-media-type="video">
                            <video class="cpv-video" src="${file}" playsinline></video>
                            <button type="button" class="cpv-video-toggle" aria-label="Play video">
                                <i class="fa-solid fa-play"></i>
                            </button>
                        </div>
                    `;
                    }
                }).join('');
                if (allMedias.length === 0) {
                    $(".cpv-img").html('');
                    if (postType !== "link") {
                        $(".cpv-link .cpv-link-img").html('');
                    }
                    return;
                }
                var firstMedia = allMedias[0];
                var firstFileType = firstMedia.dataset?.type || 'image';
                var firstFile = firstMedia.dataset?.file || firstMedia.src;
                $(".cpv-img").html(previewHtml);
                if (postType !== "link" && firstFileType == "image") {
                    $(".cpv-link .cpv-link-img").html(`<img src="${firstFile}"/>`);
                }
            }

            var container = document.querySelector('.compose-type-media .file-selected-media .items');
            if (container) {
                const observer = new MutationObserver(() => {
                    onMediaItemsChange();
                });
                observer.observe(container, {
                    childList: true,
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['src'],
                });
                onMediaItemsChange();
            } else {
                onMediaItemsChange();
            }

            if (!window.cpvVideoPreviewBound) {
                window.cpvVideoPreviewBound = true;

                document.addEventListener('click', function (event) {
                    var toggle = event.target.closest('.cpv-video-toggle');
                    var video = event.target.closest('.cpv-video');
                    var frame = event.target.closest('.cpv-video-frame');

                    if (toggle) {
                        frame = toggle.closest('.cpv-video-frame');
                        video = frame ? frame.querySelector('.cpv-video') : null;
                    }

                    if (!frame || !video) {
                        return;
                    }

                    if (video.paused) {
                        video.play();
                    } else {
                        video.pause();
                    }
                });

                document.addEventListener('play', function (event) {
                    var video = event.target;
                    if (!video.classList.contains('cpv-video')) {
                        return;
                    }
                    var frame = video.closest('.cpv-video-frame');
                    var toggle = frame ? frame.querySelector('.cpv-video-toggle') : null;
                    if (toggle) {
                        toggle.innerHTML = '<i class="fa-solid fa-pause"></i>';
                    }
                    if (frame) {
                        frame.classList.add('is-playing');
                    }
                }, true);

                document.addEventListener('pause', function (event) {
                    var video = event.target;
                    if (!video.classList.contains('cpv-video')) {
                        return;
                    }
                    var frame = video.closest('.cpv-video-frame');
                    var toggle = frame ? frame.querySelector('.cpv-video-toggle') : null;
                    if (toggle) {
                        toggle.innerHTML = '<i class="fa-solid fa-play"></i>';
                    }
                    if (frame) {
                        frame.classList.remove('is-playing');
                    }
                }, true);

                document.addEventListener('ended', function (event) {
                    var video = event.target;
                    if (!video.classList.contains('cpv-video')) {
                        return;
                    }
                    var frame = video.closest('.cpv-video-frame');
                    if (frame) {
                        frame.classList.remove('is-playing');
                    }
                }, true);
            }
        },

        AppPubishing.previewLink = function (result) {

            var data = result.data;
            var web = data.host;
            var title = data.title;
            var description = data.description;
            var image = data.image;

            if (web != "" && title != "") {
                $(".cpv-link .cpv-link-img").html(`<img src="${image}"/>`);
                $(".cpv-link .cpv-link-web").html(web);
                $(".cpv-link .cpv-link-title").html(title);
                $(".cpv-link .cpv-link-desc").html(description);
                $(".cpv-default").addClass("d-none");
            }

        },

        AppPubishing.closeCompose = function () {
            $(".compose,.compose_header").addClass("d-none");
            $(".composer-scheduling").addClass("d-none").html("");
        },

        AppPubishing.openCompose = function () {
            $(".composer-scheduling")
                .removeClass("d-none")
                .fadeIn(300);
            $(".compose,.compose_header").removeClass("d-none");
        },

        AppPubishing.composeType = function (type) {
            switch (type) {
                case "media":
                    $(".compose-type-link").addClass("d-none");
                    $(".compose-type-media").removeClass("d-none");
                    $(".compose-link-input").prop("disabled", true);
                    break;

                case "link":
                    $(".compose-type-link").removeClass("d-none");
                    $(".compose-type-media").addClass("d-none");
                    $(".compose-link-input").prop("disabled", false);
                    AppPubishing.clearThumbnailDropzone();
                    AppPubishing.clearSelectedMedia();
                    break;

                default:
                    $(".compose-type-link").addClass("d-none");
                    $(".compose-type-media").addClass("d-none");
                    $(".compose-link-input").prop("disabled", true);
                    AppPubishing.clearThumbnailDropzone();
                    AppPubishing.clearSelectedMedia();

            }

            AppPubishing.preview();
        },

        AppPubishing.shorten = function (result) {
            var emojiArea = $("[name='caption']").data("emojioneArea");
            if (result.data.caption != "" && result.data.caption !== null) {
                emojiArea.setText(result.data.caption);
            }
            $(".compose-editor [name='link']").val(result.data.link);
        },

        AppPubishing.confirmPostModal = function (result) {
            if (result.status == 2) {
                $('.data-post-confirm').html(result.errors);
                $('#confirmPostModal').modal('show');
            }
        },

        AppPubishing.reloadCalendar = function () {
            if ($(".compose-calendar").length == 0) return false;
            Calendar.refetchEvents();
        },

        AppPubishing.getThumbnailDropzoneUrl = function () {
            var $item = $('[data-thumbnail-dropzone] .items .file-item').first();
            if (!$item.length) {
                return '';
            }
            return $item.data('file') || '';
        },

        AppPubishing.setThumbnailDropzoneState = function () {
            var $dropzone = $('[data-thumbnail-dropzone]');
            if (!$dropzone.length) {
                return;
            }

            if ($dropzone.find('.items .file-item').length > 0) {
                $dropzone.find('.drophere').hide();
            } else {
                $dropzone.find('.drophere').show();
            }
        },

        AppPubishing.clearThumbnailDropzone = function () {
            var $dropzone = $('[data-thumbnail-dropzone]');
            if (!$dropzone.length) {
                return;
            }

            $dropzone.find('.items').empty();
            AppPubishing.setThumbnailDropzoneState();
            AppPubishing.preview();
        },

        AppPubishing.setThumbnailFromFileItem = function (fileItem) {
            var $fileItem = $(fileItem);
            if (!$fileItem.length) {
                return;
            }

            var type = $fileItem.data('type');
            var file = $fileItem.data('file');
            if (type !== 'image' || !file) {
                return;
            }

            var $dropzone = $('[data-thumbnail-dropzone]');
            if (!$dropzone.length) {
                return;
            }

            var itemHtml = `
                <div class="file-item w-100 ratio ratio-1x1 min-h-80 border b-r-6 rounded selected bg-primary-100 text-primary" data-file="${file}" data-type="image">
                    <label class="d-flex flex-column flex-fill">
                        <input type="text" name="link" value="${file}" class="d-none">
                        <div class="d-flex flex-fill align-items-center justify-content-center overflow-y-auto bg-cover position-relative btl-r-6 btr-r-6 file-item-media" style="background-image: url('${file}');"></div>
                    </label>
                    <button type="button" href="javascript:void(0)" class="remove bg-white border b-r-100 text-danger w-20 h-20 fs-12 position-absolute r-0"><i class="fal fa-times"></i></button>
                </div>
            `;

            $dropzone.find('.items').html(itemHtml);
            AppPubishing.setThumbnailDropzoneState();
            AppPubishing.preview();
        },

        AppPubishing.initThumbnailDropzone = function () {
            var $dropzone = $('[data-thumbnail-dropzone]');
            if (!$dropzone.length) {
                return;
            }

            AppPubishing.setThumbnailDropzoneState();

            $(document).on('click', '[data-thumbnail-dropzone] .file-item .remove', function () {
                AppPubishing.clearThumbnailDropzone();
            });

            if ($.fn.droppable) {
                $dropzone.droppable({
                    accept: '.file-item',
                    tolerance: 'pointer',
                    drop: function (event, ui) {
                        var $item = ui.draggable || ui.helper;
                        AppPubishing.setThumbnailFromFileItem($item);
                    }
                });
            }

            $(document).on('mouseover', '.file-widget .file-item, .compose-type-media .file-selected-media .items .file-item', function () {
                var $item = $(this);
                if ($item.data('type') !== 'image') {
                    return;
                }

                if ($item.hasClass('ui-draggable-handle')) {
                    return;
                }

                $item.draggable({
                    addClasses: false,
                    containment: 'document',
                    revert: 'invalid',
                    revertDuration: 200,
                    distance: 10,
                    helper: 'clone',
                    cursor: '-webkit-grabbing',
                    cursorAt: {
                        left: 35,
                        top: 35
                    },
                    start: function () {
                        $dropzone.find('.drophere').show();
                        $dropzone.find('.drophere .has-action').show();
                        $dropzone.find('.drophere .no-action').hide();
                    },
                    stop: function () {
                        $dropzone.find('.drophere .has-action').hide();
                        $dropzone.find('.drophere .no-action').show();
                        AppPubishing.setThumbnailDropzoneState();
                        $item.draggable('destroy');
                    }
                });
            });
        },

        AppPubishing.clearSelectedMedia = function () {
            var $items = $(".compose-type-media .file-selected-media .items");
            if (!$items.length) {
                return;
            }

            $items.empty();
            if (window.Files && typeof Files.checkSelectedEmpty === "function") {
                Files.checkSelectedEmpty();
            }
        },

        AppPubishing.closePopoverCalendar = function () {
            $(".fc-popover-overplay").remove();
        },

        AppPubishing.CalendarAction = function () {
            $(document).on('change', '.calendar-filter', function () {
                AppPubishing.reloadCalendar();
            });
        },

        AppPubishing.getCalendarFilters = function () {
            if ($(".compose-calendar").length == 0) return false;

            let filters = {};
            $('.calendar-filter').each(function () {
                let name = $(this).attr('name');
                let value = $(this).val();
                if (name) {
                    filters[name] = value;
                }
            });
            return filters;
        },

        AppPubishing.Calendar = function () {
            if ($(".compose-calendar").length == 0) return false;

            // Calculate the calendar height based on the main container and header
            var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
            var calendarEl = document.getElementById('calendar');
            var countClick = 0;

            Calendar = Main.Calendar(calendarEl, {
                timeZone: 'local',
                themeSystem: 'bootstrap5',
                initialView: 'dayGridMonth',
                editable: true,
                direction: document.querySelector('html').getAttribute('dir'),
                headerToolbar: {
                    center: 'title'
                },
                height: calendarHeight,
                dayMaxEvents: 2,
                displayEventTime: false,
                stickyHeaderDates: false,
                views: {
                    dayGridMonth: {
                        dayMaxEvents: 3
                    },
                    week: {
                        dayMaxEvents: 100
                    },
                    day: {}
                },
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: true,
                    meridiem: true
                },
                // Fetch events dynamically via AJAX from Laravel
                events: function (fetchInfo, successCallback, failureCallback) {
                    let filters = AppPubishing.getCalendarFilters();

                    $.ajax({
                        url: VARIABLES.url + 'app/publishing/events',
                        dataType: 'json',
                        data: {
                            // Pass start and end dates to the backend if needed
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr,
                            ...filters
                        },
                        success: function (response) {
                            // Assuming response.data is an array of event objects
                            successCallback(response.data);
                        },
                        error: function () {
                            failureCallback();
                        },

                    });
                },
                eventsSet: function (events) {
                    var currentDate = new Date();
                    currentDate.setHours(0, 0, 0, 0);

                    document.querySelectorAll('.fc-day').forEach(function (dayEl) {
                        var dateAttr = dayEl.getAttribute('data-date');
                        if (dateAttr) {
                            var date = new Date(dateAttr);
                            date.setHours(0, 0, 0, 0);
                            if (date < currentDate) {
                                dayEl.classList.add('past-day');
                            }
                        }
                    });
                },
                eventAllow: function (dropInfo, draggedEvent) {
                    return !draggedEvent.extendedProps.isPastDay;
                },
                eventDragStart: function (info) {
                    if ($(info.el).parents(".fc-day").hasClass('past-day')) {
                        Calendar.refetchEvents();
                    }
                },
                eventDrop: function (info) {
                    var $new_date = info.event.start;
                    var currentDate = new Date();
                    currentDate.setHours(0, 0, 0, 0);

                    if ($new_date < currentDate) {
                        info.revert();
                    } else {
                        Main.ConfirmDialog("Are you sure about this change?", function (s) {
                            if (!s) {
                                info.revert();
                                return false;
                            }

                            var $el = $(info.el).find('.event-item');
                            var $id = $el.data("id");
                            var $action = $el.data("url");

                            var data = new FormData();
                            if ($id != undefined) data.append("id", $id);
                            if ($new_date != undefined) data.append("new_date", $new_date);

                            Main.ajaxPost($el, $action, data, function () {

                            });
                        });
                    }
                },
                eventDidMount: function (info) {
                    var border;
                    var status;
                    var eventEl = $(info.el);
                    var eventItemEl = $('.calendar-event-item').html();
                    var data = info.event.extendedProps;
                    var media;

                    switch (data.status) {
                        case 1:
                            border = "border-dark-200";
                            status = $('.calendar-status[data-status=' + data.status + ']').html();
                            break;
                        case 3:
                            border = "border-primary-200";
                            status = $('.calendar-status[data-status=' + data.status + ']').html();
                            break;
                        case 2:
                            border = "border-warning-200";
                            status = $('.calendar-status[data-status=' + data.status + ']').html();
                            break;
                        case 4:
                            border = "border-success-200";
                            status = $('.calendar-status[data-status=' + data.status + ']').html();
                            status = status.replaceAll("[[posted_link]]", data.response.url);
                            break;
                        case 5:
                            border = "border-danger-200";
                            status = $('.calendar-status[data-status=' + data.status + ']').html();
                            status = status.replaceAll("[[msg]]", data.response.message);
                            break;
                        default:
                            border = "border-danger-200";
                            status = $('.calendar-status[data-status=5]').html();
                            break;
                    }

                    switch (data.type) {
                        case 1:
                            media = $('.calendar-media-view[data-type=' + data.type + ']').html();
                            break;
                        case 2:
                            media = $('.calendar-media-view[data-type=' + data.type + ']').html();
                            media = media.replaceAll("[[link]]", data.link);
                            break;
                        case 3:
                            if (AppPubishing.isImage(data.image)) {
                                media = '<div class="wp-100 hp-100 bg-cover b-r-6" style="background-image: url(' + data.image + ')"></div>';
                            } else if (AppPubishing.isVideo(data.image)) {
                                media = `
                                <i class="fa-solid fa-play text-white position-relative zIndex-1"></i>
                                <video muted>
                                    <source src="` + data.image + `" type="video/mp4">
                                </video>`;
                            } else {
                                media = '<div class="wp-100 hp-100 bg-cover b-r-6" style="background-image: url(' + data.image + ')"></div>';
                            }
                            break;
                        case 4:
                            media = `
                            <i class="fa-solid fa-play text-white position-relative zIndex-1"></i>
                            <video muted>
                                <source src="` + data.image + `" type="video/mp4">
                            </video>`;
                            break;
                        default:
                            media = $('.calendar-media-view[data-type=1]').html();
                            break;
                    }

                    const replacements = {
                        '[[id]]': data.id,
                        '[[icon]]': data.icon,
                        '[[color]]': data.color,
                        '[[caption]]': data.caption,
                        '[[account_name]]': data.account_name,
                        '[[time_post]]': data.time_post,
                        '[[media]]': media,
                        '[[status]]': status,
                        '[[border_color]]': border,
                    };

                    for (const [key, value] of Object.entries(replacements)) {
                        eventItemEl = eventItemEl.replaceAll(key, value);
                    }

                    if (info.view.type == "listWeek") {
                        eventEl.html('<td>' + eventItemEl + '</td>');
                    } else {
                        eventEl.html(eventItemEl);
                    }

                    //Check Pass Day
                    var date = new Date();
                    date.setHours(0, 0, 0, 0);

                    if (new Date(info.event.start) < date) {
                        info.event.setExtendedProp('isPastDay', true);
                    }

                    return false;
                },
                eventContent: function (info) {

                },
                eventChange: function () {
                    // Optional: Handle event drag-n-drop or resize actions
                },
                eventClick: function (info) {
                    var eventEl = $(info.el);
                    eventEl.parent().css('z-index', countClick + 10000);
                    countClick++;
                },
                moreLinkClick: function (info) {
                    setTimeout(function () {
                        var eventEl = $(info.el);
                        $(".fc-popover").wrap('<div class="fc-popover-overplay"></div>');
                        $(".fc-popover").removeClass("d-none");

                        const observer = new MutationObserver(function (mutationsList) {
                            mutationsList.forEach(function (mutation) {
                                mutation.removedNodes.forEach(function (removed_node) {
                                    $(".fc-popover-overplay").remove();
                                });
                            });
                        });

                        observer.observe(document.querySelector(".fc-popover-overplay"), { subtree: false, childList: true });
                    }, 10);
                }
            });

            setTimeout(() => {
                $(document).on("mouseenter", ".fc-daygrid-day", function () {
                    const $day = $(this);
                    const dateStr = $day.data("date");
                    if (!dateStr) return;

                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const hoverDate = new Date(dateStr);
                    hoverDate.setHours(0, 0, 0, 0);

                    if (hoverDate >= today && $day.find(".add-button").length === 0) {
                        // Add 15 minutes from now to hovered date
                        const now = new Date();
                        const plus15 = new Date(now.getTime() + 15 * 60000);

                        const fullDate = new Date(hoverDate);
                        fullDate.setHours(plus15.getHours());
                        fullDate.setMinutes(plus15.getMinutes());

                        const formatted = Main.formatDateTime(fullDate);

                        let addBtnHtml = $('.calendar-add-button').html();
                        addBtnHtml = addBtnHtml.replaceAll('[[date]]', encodeURIComponent(formatted));

                        $day.css("position", "relative").append($(addBtnHtml));
                    }
                });
            }, 200);

            return Calendar;
        },

        AppPubishing.isImage = function (url) {
            return /\.(jpg|jpeg|png|gif|bmp|webp|svg)$/i.test(url);
        },

        AppPubishing.isVideo = function (url) {
            return /\.(mp4|mov|webm|avi|mkv|flv|ogg)$/i.test(url);
        },

        AppPubishing.CalendarTitle = function () {
            if ($(".compose-calendar").length == 0) return false;
            var target = document.querySelector('.fc-toolbar-title');
            $(CALENDAR_SELECTORS.TITLE).html(target.innerText);
            var observer = new MutationObserver(function (mutations) {
                $(CALENDAR_SELECTORS.TITLE).html(target.innerText);
            });
            observer.observe(target, {
                childList: true,
                subtree: true,
                characterDataOldValue: true
            });
        },

        AppPubishing.CalendarEvents = function () {
            $(document).on("click", ".calendar-event", function () {
                var type = $(this).data("calendar-type");
                switch (type) {
                    case 'prev':
                        Calendar.prev();
                        break;
                    case 'next':
                        Calendar.next();
                        break;
                    case 'today':
                        Calendar.today();
                        break;
                    case 'dayGridMonth':
                        Calendar.changeView(type);
                        break;
                    case 'timeGridWeek':
                        Calendar.changeView(type);
                        break;
                    case 'listWeek':
                        Calendar.changeView(type);
                        break;
                    default:
                        Calendar.today();
                        break;
                }
            });
        },

        AppPubishing.CalendarHeight = function () {
            if ($(".compose-calendar").length == 0) return false;
            $(window).resize(function () {
                var calendarHeight = $(CALENDAR_SELECTORS.MAIN).outerHeight() - $(CALENDAR_SELECTORS.HEADER).outerHeight() - Main.getScrollbarWidth();
                Calendar.setOption('height', calendarHeight);
            });
        }

});

AppPubishing.init();
