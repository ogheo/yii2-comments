(function ($) {

    $.fn.comment = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.comment');
            return false;
        }
    };

    var commentData = {};
    var methods = {
        init: function (options) {
            return this.each(function () {
                var $comment = $(this);
                var id = $comment.attr('id');
                var settings = $.extend({}, options || {});

                if (commentData[id] === undefined) {
                    commentData[id] = {};
                } else {
                    return;
                }

                commentData[id] = $.extend(commentData[id], {settings: settings});

                var wrapperId = commentData[id].settings.wrapperId;
                var formSelector = commentData[id].settings.formSelector;
                var showCommentsId = commentData[id].settings.showCommentsId;
                var fullCommentsId = commentData[id].settings.fullCommentsId;
                var pjaxContainer = commentData[id].settings.pjaxContainerId;
                var formContainer = commentData[id].settings.formContainerId;
                var submitButtonId = commentData[id].settings.submitButtonId;
                var postButtonName = commentData[id].settings.postButtonName;
                var replyButtonName = commentData[id].settings.replyButtonName;
                var ratingUrl = commentData[id].settings.ratingUrl;
                var pagination = pjaxContainer + ' .pagination a';
                var formTextarea = formSelector + ' textarea';
                var eventParams = {
                    ratingUrl: ratingUrl,
                    wrapperId: wrapperId,
                    formSelector: formSelector,
                    showCommentsId: showCommentsId,
                    fullCommentsId: fullCommentsId,
                    pjaxContainer: pjaxContainer,
                    formContainer: formContainer,
                    submitButtonId: submitButtonId,
                    postButtonName: postButtonName,
                    replyButtonName: replyButtonName
                };

                $comment.on('beforeSubmit.comment', formSelector, eventParams, createComment);
                $comment.on('click.comment', '[data-action="reply"]', eventParams, reply);
                $comment.on('click.comment', '[data-action="cancel-reply"]', eventParams, cancelReply);
                $comment.on('click.comment', '[data-action="show-comments"]', eventParams, showComments);
                $comment.on('click.comment', '[data-action="hide-comments"]', eventParams, hideComments);
                $comment.on('click.comment', '[data-action="downrate"]', eventParams, downrate);
                $comment.on('click.comment', '[data-action="uprate"]', eventParams, uprate);
                $comment.on('click.comment', pagination, eventParams, scrollTo);
                $comment.on('focus.comment', formTextarea, showButtons);
            });
        }
    };

    /**
     * Show comments section
     * @param event
     */
    function showComments(event) {
        var showCommentsId = event.data.showCommentsId;
        var fullCommentsId = event.data.fullCommentsId;

        $(showCommentsId).fadeOut(300, function () {
            $(showCommentsId).addClass('hidden')
        });
        $(fullCommentsId).fadeIn(300, function () {
            $(fullCommentsId).removeClass('hidden')
        });

        return false;
    }

    /**
     * Hide comments section
     * @param event
     */
    function hideComments(event) {
        var showCommentsId = event.data.showCommentsId;
        var fullCommentsId = event.data.fullCommentsId;

        $(fullCommentsId).fadeOut(300, function () {
            $(fullCommentsId).addClass('hidden')
        });
        $(showCommentsId).fadeIn(300, function () {
            $(showCommentsId).removeClass('hidden')
        });

        return false;
    }

    /**
     * Scroll to the top of comment section
     * @param event
     */
    function scrollTo(event) {
        var wrapperId = event.data.wrapperId;
        $('html, body').animate({
            scrollTop: ($(wrapperId).offset().top)
        }, 500);
    }

    /**
     * Show form buttons
     */
    function showButtons() {
        $(this).parents('form').find('.media-buttons').fadeIn('fast');
    }

    /**
     * Create a comment
     * @returns {boolean}
     */
    function createComment(event) {
        var $commentForm = $(this);
        var pjaxContainer = event.data.pjaxContainer;
        var formData = $commentForm.serializeArray();
        var parentId = $commentForm.parents('[data-key]').data('key');

        if (parentId) {
            formData.push({'name': 'Comments[parent_id]', 'value': parentId});
        }

        $.ajax({
            type: 'post',
            data: formData,
            url: $commentForm.attr('action'),
            success: function (response) {
                if (response['status'] === 'success') {
                    $.pjax({
                        url: window.location.href,
                        container: pjaxContainer,
                        scrollTo: false,
                        timeout: 10000
                    });
                } else {
                    if (response.hasOwnProperty('errors')) {
                        $commentForm.yiiActiveForm('updateMessages', response.errors, true);
                    }
                }
            }
        });

        return false;
    }

    /**
     * Reply to comment
     * @param event
     */
    function reply(event) {
        var $this = $(this);
        var $commentsFormContainer = $(event.data.formContainer);
        var parentCommentSelector = $this.parents('.media-info');
        var replyButtonName = event.data.replyButtonName;
        var submitButton = event.data.submitButtonId;

        $commentsFormContainer.find(submitButton).html(replyButtonName);
        $commentsFormContainer.appendTo(parentCommentSelector);
        $commentsFormContainer.find('form textarea').focus();

        return false;
    }

    /**
     * Cancel reply
     * @param event
     */
    function cancelReply(event) {
        var $this = $(this);
        var $commentsForm = $(event.data.formSelector);
        var $commentsFormContainer = $(event.data.formContainer);
        var postButtonName = event.data.postButtonName;
        var submitButton = event.data.submitButtonId;

        $commentsFormContainer.find(submitButton).html(postButtonName);
        if ($this.parents('[data-key]').length) {
            $commentsForm.trigger("reset");
            $this.parents('.media-buttons').hide();
            $commentsFormContainer.hide().insertAfter('#comments-container-header').fadeIn("fast");
        } else {
            $commentsForm.trigger("reset");
            $this.parents('.media-buttons').fadeOut('fast');
        }

        return false;
    }

    /**
     * Rate comment -
     * @param event
     * @returns {boolean}
     */
    function downrate(event) {
        var $downrateButton = $(this);
        var $ratingUrl = event.data.ratingUrl;
        var downrateFormData = $downrateButton.serializeArray();
        var commentId = $downrateButton.parents('[data-key]').data('key');

        if (commentId) {
            downrateFormData.push({'name': 'CommentsRating[comment_id]', 'value': commentId});
            downrateFormData.push({'name': 'CommentsRating[status]', 'value': 2});
        }

        $.ajax({
            type: 'post',
            data: downrateFormData,
            url: $ratingUrl,
            success: function (response) {
                if (response['status'] === 'success') {
                    var commentRating = $downrateButton.parents('.comment-rating');
                    var $uprateButton = commentRating.find('[data-action="uprate"]');
                    var scoreId = commentRating.find('#score');
                    var score = parseInt($(scoreId).html());

                    if (response['action'] === 'rated') {
                        score = score - 1;
                        $(scoreId).html(score);
                        $downrateButton.addClass('rated');
                    } else if (response['action'] === 'updated-') {
                        score = score - 2;
                        $(scoreId).html(score);
                        $uprateButton.removeClass('rated');
                        $downrateButton.addClass('rated');
                    } else if (response['action'] === 'unrated') {
                        score = score + 1;
                        $(scoreId).html(score);
                        $downrateButton.removeClass('rated');
                    }

                    if (score < 0) {
                        $(scoreId).removeClass().addClass('bad');
                    } else {
                        $(scoreId).removeClass().addClass('good');
                    }
                }
            }
        });

        return false;
    }

    /**
     * Rate comment +
     * @param event
     * @returns {boolean}
     */
    function uprate(event) {
        var $uprateButton = $(this);
        var $ratingUrl = event.data.ratingUrl;
        var uprateFormData = $uprateButton.serializeArray();
        var commentId = $uprateButton.parents('[data-key]').data('key');

        if (commentId) {
            uprateFormData.push({'name': 'CommentsRating[comment_id]', 'value': commentId});
            uprateFormData.push({'name': 'CommentsRating[status]', 'value': 1});
        }

        $.ajax({
            type: 'post',
            data: uprateFormData,
            url: $ratingUrl,
            success: function (response) {
                if (response['status'] === 'success') {
                    var commentRating = $uprateButton.parents('.comment-rating');
                    var $downrateButton = commentRating.find('[data-action="downrate"]');
                    var scoreId = commentRating.find('#score');
                    var score = parseInt($(scoreId).html());

                    if (response['action'] === 'rated') {
                        score = score + 1;
                        $(scoreId).html(score);
                        $uprateButton.addClass('rated');
                    } else if (response['action'] === 'updated+') {
                        score = score + 2;
                        $(scoreId).html(score);
                        $downrateButton.removeClass('rated');
                        $uprateButton.addClass('rated');
                    } else if (response['action'] === 'unrated') {
                        score = score - 1;
                        $(scoreId).html(score);
                        $uprateButton.removeClass('rated');
                    }

                    if (score < 0) {
                        $(scoreId).removeClass().addClass('bad');
                    } else {
                        $(scoreId).removeClass().addClass('good');
                    }
                }
            }
        });

        return false;
    }

})(window.jQuery);
