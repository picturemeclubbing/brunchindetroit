<?php
declare(strict_types=1);

/**
 * File: app/views/partials/rsvp-modal.php
 * Purpose: Single reusable RSVP modal shared by every public page. Hidden by
 *          default; opened by any element with [data-rsvp-trigger] via
 *          public/assets/js/rsvp.js, which reads that trigger's
 *          data-rsvp-venue-slug / data-rsvp-venue-id / data-rsvp-venue-name
 *          attributes and posts to public/rsvp-submit.php as JSON/AJAX.
 *          Date/time fields use a small Google Calendar-style popover picker
 *          (calendar grid + time-slot list) instead of native <input
 *          type="date"/"time">, driven entirely by public/assets/js/rsvp.js.
 *          Included once from app/views/partials/footer.php so it is present
 *          on every page without duplicating markup per view.
 * Batch: B2 RSVP modal + public wiring (date/time picker added in the B2
 *        repair pass).
 */
?>
<div class="rsvp-modal" id="rsvpModal" hidden aria-hidden="true">
    <div class="rsvp-modal__backdrop" data-rsvp-close></div>

    <div class="rsvp-modal__panel" role="dialog" aria-modal="true" aria-labelledby="rsvpModalTitle">
        <div class="rsvp-modal__header">
            <div>
                <h2 class="rsvp-modal__title" id="rsvpModalTitle">Request a Reservation</h2>
                <p class="rsvp-modal__subtitle" data-rsvp-venue-display>Let us know when you would like to visit.</p>
            </div>
            <button type="button" class="rsvp-modal__close" data-rsvp-close aria-label="Close">
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>
        </div>

        <div class="rsvp-modal__body">
            <div class="rsvp-modal__confirmation" data-rsvp-confirmation hidden>
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <p data-rsvp-confirmation-message>Thanks! Your request has been received.</p>
            </div>
            <div class="rsvp-modal__alert rsvp-modal__alert--danger" data-rsvp-error hidden>
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                <span data-rsvp-error-message>Please fix the highlighted fields.</span>
            </div>

            <form class="rsvp-modal__form" id="rsvpForm" method="post" action="<?= e(asset_url('rsvp-submit.php')) ?>" novalidate>
                <input type="hidden" name="venue_slug" data-rsvp-field="venue_slug" value="">
                <input type="hidden" name="venue_id" data-rsvp-field="venue_id" value="">
                <input type="hidden" name="source_context" data-rsvp-field="source_context" value="">

                <!-- Honeypot: hidden from real visitors via CSS; bots that fill
                     every field will trip this and be quietly ignored server-side. -->
                <div class="rsvp-modal__honeypot" aria-hidden="true">
                    <label for="rsvpWebsite">Leave this field blank</label>
                    <input type="text" id="rsvpWebsite" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="rsvp-modal__grid">
                    <div class="rsvp-modal__field rsvp-modal__field--full">
                        <label class="form-label" for="rsvpName">Name <span class="rsvp-modal__req">*</span></label>
                        <input type="text" id="rsvpName" name="name" class="form-control" maxlength="150" required>
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="name"></span>
                    </div>

                    <div class="rsvp-modal__field">
                        <label class="form-label" for="rsvpPhone">Phone</label>
                        <input type="tel" id="rsvpPhone" name="phone" class="form-control" maxlength="30" autocomplete="tel">
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="phone"></span>
                    </div>

                    <div class="rsvp-modal__field">
                        <label class="form-label" for="rsvpEmail">Email</label>
                        <input type="email" id="rsvpEmail" name="email" class="form-control" maxlength="190" autocomplete="email">
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="email"></span>
                    </div>

                    <p class="rsvp-modal__hint rsvp-modal__field--full" data-rsvp-field-error="contact"></p>

                    <div class="rsvp-modal__field">
                        <label class="form-label" for="rsvpPartySize">Party Size <span class="rsvp-modal__req">*</span></label>
                        <input type="number" id="rsvpPartySize" name="party_size" class="form-control" min="1" max="50" inputmode="numeric" required>
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="party_size"></span>
                    </div>

                    <div class="rsvp-modal__field">
                        <label class="form-label" for="rsvpDateDisplay">Date <span class="rsvp-modal__req">*</span></label>
                        <div class="rsvp-datepicker" data-rsvp-datepicker>
                            <button type="button" class="form-control rsvp-datepicker__input" id="rsvpDateDisplay" data-rsvp-datepicker-toggle aria-haspopup="true" aria-expanded="false">
                                <span data-rsvp-datepicker-label>Select a date</span>
                                <i class="fas fa-calendar-days" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="requested_date" data-rsvp-datepicker-value required>
                            <div class="rsvp-datepicker__panel" data-rsvp-datepicker-panel hidden>
                                <div class="rsvp-datepicker__header">
                                    <button type="button" class="rsvp-datepicker__nav" data-rsvp-datepicker-prev aria-label="Previous month">
                                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                                    </button>
                                    <span data-rsvp-datepicker-month></span>
                                    <button type="button" class="rsvp-datepicker__nav" data-rsvp-datepicker-next aria-label="Next month">
                                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="rsvp-datepicker__weekdays">
                                    <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                                </div>
                                <div class="rsvp-datepicker__days" data-rsvp-datepicker-days></div>
                            </div>
                        </div>
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="requested_date"></span>
                    </div>

                    <div class="rsvp-modal__field">
                        <label class="form-label" for="rsvpTimeDisplay">Time <span class="rsvp-modal__req">*</span></label>
                        <div class="rsvp-timepicker" data-rsvp-timepicker>
                            <button type="button" class="form-control rsvp-timepicker__input" id="rsvpTimeDisplay" data-rsvp-timepicker-toggle aria-haspopup="true" aria-expanded="false">
                                <span data-rsvp-timepicker-label>Select a time</span>
                                <i class="fas fa-clock" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="requested_time" data-rsvp-timepicker-value required>
                            <ul class="rsvp-timepicker__panel" data-rsvp-timepicker-panel hidden></ul>
                        </div>
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="requested_time"></span>
                    </div>

                    <div class="rsvp-modal__field rsvp-modal__field--full">
                        <label class="form-label" for="rsvpNotes">Notes</label>
                        <textarea id="rsvpNotes" name="notes" class="form-control" rows="3" maxlength="1000"></textarea>
                        <span class="rsvp-modal__field-error" data-rsvp-field-error="notes"></span>
                    </div>
                </div>

                <div class="rsvp-modal__actions">
                    <button type="button" class="btn btn--outline" data-rsvp-close>Cancel</button>
                    <button type="submit" class="btn btn--primary" data-rsvp-submit>
                        <i class="fas fa-calendar-check" aria-hidden="true"></i>
                        Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
