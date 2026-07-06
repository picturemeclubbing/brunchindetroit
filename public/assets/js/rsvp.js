/**
 * File: public/assets/js/rsvp.js
 * Purpose: Opens/closes the shared #rsvpModal (app/views/partials/rsvp-modal.php)
 *          from any [data-rsvp-trigger] element, and submits the form via
 *          fetch() as AJAX to public/rsvp-submit.php, showing inline success
 *          or validation errors without a page redirect. Also drives the
 *          Google Calendar-style date/time popover pickers (a small month
 *          grid for the date, a scrollable time-slot list for the time) and
 *          mirrors the server's required-field rules client-side so users
 *          get instant feedback before the AJAX round trip.
 * Batch: B2 RSVP modal + public wiring (date/time pickers + stricter client
 *        validation added in the B2 repair pass).
 *
 * Notes:
 *   - No framework / no dependencies, matching public/assets/js/main.js and
 *     public/assets/js/venue-menu.js.
 *   - Runs after DOMContentLoaded (or immediately if the DOM is already ready).
 *   - If the modal or trigger buttons are missing on a given page, the script
 *     does nothing safely (no thrown errors).
 *   - Trigger buttons pass venue identity via data-rsvp-venue-slug /
 *     data-rsvp-venue-id / data-rsvp-venue-name, and optionally
 *     data-rsvp-source for the source_context field.
 *   - Client-side validation here is a UX convenience only — the server
 *     (public/rsvp-submit.php) is always the source of truth and re-checks
 *     everything, including brunch-hours availability where configured.
 */
(function () {
    "use strict";

    function ready(fn) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", fn);
        } else {
            fn();
        }
    }

    function pad2(number) {
        return number < 10 ? "0" + number : String(number);
    }

    var MONTH_NAMES = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    var WEEKDAY_SHORT = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    ready(function () {
        var modal = document.getElementById("rsvpModal");
        var triggers = document.querySelectorAll("[data-rsvp-trigger]");

        if (!modal || !triggers.length) {
            return;
        }

        var form = modal.querySelector("#rsvpForm");
        var venueDisplay = modal.querySelector("[data-rsvp-venue-display]");
        var confirmationView = modal.querySelector("[data-rsvp-confirmation]");
        var confirmationMessage = modal.querySelector("[data-rsvp-confirmation-message]");
        var errorAlert = modal.querySelector("[data-rsvp-error]");
        var errorMessage = modal.querySelector("[data-rsvp-error-message]");
        var submitButton = modal.querySelector("[data-rsvp-submit]");
        var lastFocusedElement = null;
        var successCloseTimer = null;

        // How long the confirmation view stays visible before the modal
        // auto-closes and returns the user to the page underneath.
        var SUCCESS_AUTOCLOSE_MS = 1350;

        if (!form) {
            return;
        }

        function fieldInput(name) {
            return form.querySelector('[name="' + name + '"]');
        }

        function clearFieldErrors() {
            var errorSpans = modal.querySelectorAll("[data-rsvp-field-error]");
            Array.prototype.forEach.call(errorSpans, function (span) {
                span.textContent = "";
            });
        }

        function showFieldErrors(errors) {
            if (!errors) {
                return;
            }
            Object.keys(errors).forEach(function (key) {
                var span = modal.querySelector('[data-rsvp-field-error="' + key + '"]');
                if (span) {
                    span.textContent = errors[key];
                }
            });
        }

        function hideAlerts() {
            if (errorAlert) {
                errorAlert.hidden = true;
            }
        }

        function clearSuccessCloseTimer() {
            if (successCloseTimer) {
                window.clearTimeout(successCloseTimer);
                successCloseTimer = null;
            }
        }

        // Shows the form again and hides the confirmation view. Called when
        // the modal is (re)opened so a later RSVP always starts from a clean
        // visible form, never the previous confirmation state.
        function showForm() {
            clearSuccessCloseTimer();
            if (form) {
                form.hidden = false;
            }
            if (confirmationView) {
                confirmationView.hidden = true;
            }
        }

        // Replaces the form with a confirmation view (does not show the form
        // and the message side-by-side), then auto-closes the modal shortly
        // after so the user is returned to the page underneath.
        function showConfirmationAndClose(message) {
            clearFieldErrors();
            hideAlerts();

            if (confirmationMessage && message) {
                confirmationMessage.textContent = message;
            }
            if (form) {
                form.hidden = true;
            }
            if (confirmationView) {
                confirmationView.hidden = false;
            }

            clearSuccessCloseTimer();
            successCloseTimer = window.setTimeout(function () {
                closeModal();
            }, SUCCESS_AUTOCLOSE_MS);
        }

        function showError(message) {
            hideAlerts();
            if (errorMessage && message) {
                errorMessage.textContent = message;
            }
            if (errorAlert) {
                errorAlert.hidden = false;
            }
        }

        /* -------------------------------------------------------------- */
        /* Date picker: small Google Calendar-style month grid popover.    */
        /* -------------------------------------------------------------- */
        var datePickerRoot = form.querySelector("[data-rsvp-datepicker]");
        var datePickerToggle = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-toggle]") : null;
        var datePickerPanel = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-panel]") : null;
        var datePickerValue = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-value]") : null;
        var datePickerLabel = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-label]") : null;
        var datePickerMonthLabel = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-month]") : null;
        var datePickerDaysContainer = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-days]") : null;
        var datePickerPrev = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-prev]") : null;
        var datePickerNext = datePickerRoot ? datePickerRoot.querySelector("[data-rsvp-datepicker-next]") : null;

        var today = new Date();
        today.setHours(0, 0, 0, 0);

        var viewYear = today.getFullYear();
        var viewMonth = today.getMonth();

        function formatDateValue(year, month, day) {
            return year + "-" + pad2(month + 1) + "-" + pad2(day);
        }

        function formatDateLabel(year, month, day) {
            var d = new Date(year, month, day);
            return WEEKDAY_SHORT[d.getDay()] + ", " + MONTH_NAMES[month].slice(0, 3) + " " + day + ", " + year;
        }

        function closeDatePicker() {
            if (!datePickerPanel) {
                return;
            }
            datePickerPanel.hidden = true;
            if (datePickerToggle) {
                datePickerToggle.setAttribute("aria-expanded", "false");
            }
        }

        function renderCalendar() {
            if (!datePickerDaysContainer || !datePickerMonthLabel) {
                return;
            }

            datePickerMonthLabel.textContent = MONTH_NAMES[viewMonth] + " " + viewYear;
            datePickerDaysContainer.innerHTML = "";

            var selectedValue = datePickerValue ? datePickerValue.value : "";
            var firstOfMonth = new Date(viewYear, viewMonth, 1);
            var startWeekday = firstOfMonth.getDay();
            var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            var totalCells = startWeekday + daysInMonth;
            var trailingCells = (7 - (totalCells % 7)) % 7;
            var totalGridCells = totalCells + trailingCells;

            for (var i = 0; i < totalGridCells; i++) {
                var dayNumber = i - startWeekday + 1;
                var cell = document.createElement("button");
                cell.type = "button";
                cell.className = "rsvp-datepicker__day";

                if (dayNumber < 1 || dayNumber > daysInMonth) {
                    cell.className += " rsvp-datepicker__day--empty";
                    cell.disabled = true;
                    cell.setAttribute("aria-hidden", "true");
                    cell.tabIndex = -1;
                } else {
                    cell.textContent = String(dayNumber);
                    var cellDate = new Date(viewYear, viewMonth, dayNumber);
                    var cellValue = formatDateValue(viewYear, viewMonth, dayNumber);

                    if (cellDate < today) {
                        cell.disabled = true;
                        cell.className += " rsvp-datepicker__day--disabled";
                    }

                    if (cellValue === selectedValue) {
                        cell.className += " rsvp-datepicker__day--selected";
                    }

                    (function (value, label) {
                        cell.addEventListener("click", function (event) {
                            event.stopPropagation();
                            if (datePickerValue) {
                                datePickerValue.value = value;
                            }
                            if (datePickerLabel) {
                                datePickerLabel.textContent = label;
                            }
                            var errorSpan = modal.querySelector('[data-rsvp-field-error="requested_date"]');
                            if (errorSpan) {
                                errorSpan.textContent = "";
                            }
                            closeDatePicker();
                        });
                    })(cellValue, formatDateLabel(viewYear, viewMonth, dayNumber));
                }

                datePickerDaysContainer.appendChild(cell);
            }
        }

        function openDatePicker() {
            if (!datePickerPanel) {
                return;
            }
            closeTimePicker();
            renderCalendar();
            datePickerPanel.hidden = false;
            if (datePickerToggle) {
                datePickerToggle.setAttribute("aria-expanded", "true");
            }
        }

        if (datePickerToggle && datePickerPanel) {
            datePickerToggle.addEventListener("click", function (event) {
                event.stopPropagation();
                if (datePickerPanel.hidden) {
                    openDatePicker();
                } else {
                    closeDatePicker();
                }
            });

            datePickerPanel.addEventListener("click", function (event) {
                event.stopPropagation();
            });
        }

        if (datePickerPrev) {
            datePickerPrev.addEventListener("click", function (event) {
                event.stopPropagation();
                viewMonth -= 1;
                if (viewMonth < 0) {
                    viewMonth = 11;
                    viewYear -= 1;
                }
                renderCalendar();
            });
        }

        if (datePickerNext) {
            datePickerNext.addEventListener("click", function (event) {
                event.stopPropagation();
                viewMonth += 1;
                if (viewMonth > 11) {
                    viewMonth = 0;
                    viewYear += 1;
                }
                renderCalendar();
            });
        }

        /* -------------------------------------------------------------- */
        /* Time picker: scrollable list of time slots, brunch-hours range. */
        /* -------------------------------------------------------------- */
        var timePickerRoot = form.querySelector("[data-rsvp-timepicker]");
        var timePickerToggle = timePickerRoot ? timePickerRoot.querySelector("[data-rsvp-timepicker-toggle]") : null;
        var timePickerPanel = timePickerRoot ? timePickerRoot.querySelector("[data-rsvp-timepicker-panel]") : null;
        var timePickerValue = timePickerRoot ? timePickerRoot.querySelector("[data-rsvp-timepicker-value]") : null;
        var timePickerLabel = timePickerRoot ? timePickerRoot.querySelector("[data-rsvp-timepicker-label]") : null;

        // Typical brunch service window. This is just the picker's UI range —
        // the server (public/rsvp-submit.php) is the real source of truth and
        // will additionally check a venue's actual brunch hours where that
        // data has been configured.
        var TIME_SLOT_START_MINUTES = 7 * 60;
        var TIME_SLOT_END_MINUTES = 15 * 60;
        var TIME_SLOT_STEP_MINUTES = 30;

        function formatTimeLabel(hour24, minute) {
            var period = hour24 >= 12 ? "PM" : "AM";
            var hour12 = hour24 % 12;
            if (hour12 === 0) {
                hour12 = 12;
            }
            return hour12 + ":" + pad2(minute) + " " + period;
        }

        function closeTimePicker() {
            if (!timePickerPanel) {
                return;
            }
            timePickerPanel.hidden = true;
            if (timePickerToggle) {
                timePickerToggle.setAttribute("aria-expanded", "false");
            }
        }

        function renderTimeList() {
            if (!timePickerPanel) {
                return;
            }
            timePickerPanel.innerHTML = "";

            var selectedValue = timePickerValue ? timePickerValue.value : "";

            for (var minutes = TIME_SLOT_START_MINUTES; minutes <= TIME_SLOT_END_MINUTES; minutes += TIME_SLOT_STEP_MINUTES) {
                var hour24 = Math.floor(minutes / 60);
                var minute = minutes % 60;
                var value = pad2(hour24) + ":" + pad2(minute);
                var label = formatTimeLabel(hour24, minute);

                var li = document.createElement("li");
                var button = document.createElement("button");
                button.type = "button";
                button.className = "rsvp-timepicker__option";
                if (value === selectedValue) {
                    button.className += " rsvp-timepicker__option--selected";
                }
                button.textContent = label;

                (function (value, label) {
                    button.addEventListener("click", function (event) {
                        event.stopPropagation();
                        if (timePickerValue) {
                            timePickerValue.value = value;
                        }
                        if (timePickerLabel) {
                            timePickerLabel.textContent = label;
                        }
                        var errorSpan = modal.querySelector('[data-rsvp-field-error="requested_time"]');
                        if (errorSpan) {
                            errorSpan.textContent = "";
                        }
                        closeTimePicker();
                    });
                })(value, label);

                li.appendChild(button);
                timePickerPanel.appendChild(li);
            }
        }

        function openTimePicker() {
            if (!timePickerPanel) {
                return;
            }
            closeDatePicker();
            renderTimeList();
            timePickerPanel.hidden = false;
            if (timePickerToggle) {
                timePickerToggle.setAttribute("aria-expanded", "true");
            }
        }

        if (timePickerToggle && timePickerPanel) {
            timePickerToggle.addEventListener("click", function (event) {
                event.stopPropagation();
                if (timePickerPanel.hidden) {
                    openTimePicker();
                } else {
                    closeTimePicker();
                }
            });

            timePickerPanel.addEventListener("click", function (event) {
                event.stopPropagation();
            });
        }

        // Any click elsewhere on the page closes both popovers.
        document.addEventListener("click", function () {
            closeDatePicker();
            closeTimePicker();
        });

        function resetPickers() {
            viewYear = today.getFullYear();
            viewMonth = today.getMonth();

            if (datePickerValue) {
                datePickerValue.value = "";
            }
            if (datePickerLabel) {
                datePickerLabel.textContent = "Select a date";
            }
            closeDatePicker();

            if (timePickerValue) {
                timePickerValue.value = "";
            }
            if (timePickerLabel) {
                timePickerLabel.textContent = "Select a time";
            }
            closeTimePicker();
        }

        /* -------------------------------------------------------------- */
        /* Modal open/close                                                */
        /* -------------------------------------------------------------- */
        function resetForm() {
            form.reset();
            resetPickers();
            clearFieldErrors();
            hideAlerts();
            showForm();
        }

        function openModal(trigger) {
            lastFocusedElement = document.activeElement;

            var venueSlug = trigger.getAttribute("data-rsvp-venue-slug") || "";
            var venueId = trigger.getAttribute("data-rsvp-venue-id") || "";
            var venueName = trigger.getAttribute("data-rsvp-venue-name") || "";
            var source = trigger.getAttribute("data-rsvp-source") || "";

            resetForm();

            var slugField = fieldInput("venue_slug");
            var idField = fieldInput("venue_id");
            var sourceField = fieldInput("source_context");

            if (slugField) {
                slugField.value = venueSlug;
            }
            if (idField) {
                idField.value = venueId && venueId !== "0" ? venueId : "";
            }
            if (sourceField) {
                sourceField.value = source;
            }

            if (venueDisplay) {
                venueDisplay.textContent = venueName
                    ? "Requesting a reservation at " + venueName + "."
                    : "Let us know when you would like to visit.";
            }

            modal.hidden = false;
            modal.setAttribute("aria-hidden", "false");
            document.body.classList.add("rsvp-modal-open");

            var nameField = fieldInput("name");
            if (nameField) {
                window.setTimeout(function () {
                    nameField.focus();
                }, 0);
            }
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute("aria-hidden", "true");
            document.body.classList.remove("rsvp-modal-open");
            closeDatePicker();
            closeTimePicker();
            clearSuccessCloseTimer();

            if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
                lastFocusedElement.focus();
            }
        }

        Array.prototype.forEach.call(triggers, function (trigger) {
            trigger.addEventListener("click", function () {
                openModal(trigger);
            });
        });

        var closeElements = modal.querySelectorAll("[data-rsvp-close]");
        Array.prototype.forEach.call(closeElements, function (element) {
            element.addEventListener("click", function () {
                closeModal();
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key !== "Escape") {
                return;
            }

            var datePickerOpen = datePickerPanel && !datePickerPanel.hidden;
            var timePickerOpen = timePickerPanel && !timePickerPanel.hidden;

            if (datePickerOpen || timePickerOpen) {
                closeDatePicker();
                closeTimePicker();
                return;
            }

            if (!modal.hidden) {
                closeModal();
            }
        });

        /* -------------------------------------------------------------- */
        /* Client-side validation (UX convenience only — server is the     */
        /* source of truth and re-validates everything, including brunch   */
        /* hours where configured).                                       */
        /* -------------------------------------------------------------- */
        function validateClientSide() {
            var errors = {};

            var nameVal = (fieldInput("name") || {}).value || "";
            var phoneVal = (fieldInput("phone") || {}).value || "";
            var emailVal = (fieldInput("email") || {}).value || "";
            var partySizeVal = (fieldInput("party_size") || {}).value || "";
            var dateVal = datePickerValue ? datePickerValue.value : "";
            var timeVal = timePickerValue ? timePickerValue.value : "";

            if (!nameVal.trim()) {
                errors.name = "Name is required.";
            }
            if (!phoneVal.trim() && !emailVal.trim()) {
                errors.contact = "Please provide a phone number or email address.";
            }
            if (!partySizeVal.trim()) {
                errors.party_size = "Party size is required.";
            }
            if (!dateVal) {
                errors.requested_date = "Requested date is required.";
            }
            if (!timeVal) {
                errors.requested_time = "Requested time is required.";
            }

            return errors;
        }

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            hideAlerts();
            clearFieldErrors();

            var clientErrors = validateClientSide();
            if (Object.keys(clientErrors).length > 0) {
                showFieldErrors(clientErrors);
                showError("Please fix the highlighted fields.");
                return;
            }

            if (submitButton) {
                submitButton.disabled = true;
            }

            var formData = new FormData(form);

            fetch(form.getAttribute("action"), {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "rsvp-ajax" }
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    var data = result.data;

                    if (data && data.success) {
                        form.reset();
                        resetPickers();
                        showConfirmationAndClose(data.message);
                    } else if (result.status === 422 && data && data.errors) {
                        showFieldErrors(data.errors);
                        showError(data.message || "Please fix the highlighted fields.");
                    } else {
                        showError((data && data.message) || "Something went wrong. Please try again.");
                    }
                })
                .catch(function () {
                    showError("Something went wrong. Please check your connection and try again.");
                })
                .then(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                });
        });
    });
})();
