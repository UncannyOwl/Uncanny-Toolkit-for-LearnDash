class Notifications {

    constructor() {

        // If no notification, bail.
        if (!this.hasNotification) {
            return;
        }

        this.registerActions();

        this.state = {
            itemsCount: parseInt(ToolkitNotifications.itemsCount),
            lastIndex: parseInt(ToolkitNotifications.lastIndex)
        };

        this.updateNavigation();

    }

    registerActions() {

        var controller = this;

        let nextBtns = document.getElementsByClassName('uo-notifications-controller-next');

        let prevBtns = document.getElementsByClassName('uo-notifications-controller-prev');

        let dismiss = document.getElementsByClassName('uo-notifications-action-dismiss');

        // Next buttons.
        Array.from(nextBtns).forEach(function (element) {

            element.addEventListener('click', function () {

                let targetParent = element.parentNode.parentNode.parentNode.parentNode;

                targetParent.classList.remove('active');

                targetParent.nextElementSibling.classList.add('active');

                element.parentNode.parentNode.parentNode.classList.remove('active');

            });

        });

        // Previous buttons.
        Array.from(prevBtns).forEach(function (element) {

            element.addEventListener('click', function () {

                let targetParent = element.parentNode.parentNode.parentNode.parentNode;

                targetParent.classList.remove('active');

                if (targetParent.previousElementSibling) {

                    targetParent.previousElementSibling.classList.add('active');

                }

                element.parentNode.parentNode.parentNode.classList.remove('active');

            });

        });

        // Dismiss
        Array.from(dismiss).forEach(function (element) {

            element.addEventListener('click', function () {

                let notification_id = element.getAttribute('data-notification-id');

                controller.dismissNotification(notification_id, element);

            });

        });
    }

    dismissNotification(notification_id, element) {

        element.classList.add('uo-notifications-action-dismiss--loading');

        let notificationObj = this;

        jQuery.ajax({

            url: ToolkitNotifications.ajaxurl,

            data: {
                action: 'uncanny_owl_notification_dismiss',
                nonce: ToolkitNotifications.nonce,
                id: notification_id
            },

            success: function (response) {

                element.classList.remove('uo-notifications-action-dismiss--loading');

                if (response.success) {

                    let currentItem = element.parentNode.parentNode.parentNode;

                    currentItem.classList.remove('active');

                    // Activate next item.
                    if (parseInt(currentItem.getAttribute('data-index')) !== notificationObj.state.lastIndex) {

                        if (currentItem.nextElementSibling) {

                            currentItem.nextElementSibling.classList.add('active');

                        } else {

                            // Go back to first element if there is no sibling.
                            document.querySelector('ul.uo-notifications-list li:first-child').classList.add('active');

                        }

                    } else {

                        // Go back to the first element if state has last index.
                        document.querySelector('ul.uo-notifications-list li:first-child').classList.add('active');

                    }

                    // Delete all element if there are no remaining.
                    if (notificationObj.state.itemsCount === 1) {

                        document.querySelector('.uo-notifications').remove();

                    }

                    notificationObj.state.itemsCount--;

                    currentItem.remove();

                    notificationObj.updateNavigation();

                }

            },
            error: function (response) {
                console.log(response);
            }
        });

    }

    updateNavigation() {

        let $prevButton = document.querySelector('ul.uo-notifications-list li:first-child').querySelector('.uo-notifications-controller-prev');

        let $nextButton = document.querySelector('ul.uo-notifications-list li:last-child').querySelector('.uo-notifications-controller-next');

        if ($prevButton) {
            $prevButton.setAttribute('disabled', true);
        }

        if ($nextButton) {
            $nextButton.setAttribute('disabled', true);
        }

    }

    get hasNotification() {

        return (null !== document.getElementById('uo-notifications-wrap'));

    }

}

jQuery(document).ready(function ($) {
    new Notifications();
});
