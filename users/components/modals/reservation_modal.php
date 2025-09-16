
<!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <h4 class="modal-title" id="reservationModalLabel">Room Request</h4>
                    <p class="modal-subtitle">Fill out the form to request a room for your activity</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reservationModalContent">
                <div class="reservation-card">
                    <!-- Reservation Form -->
                    <form id="reservationForm" method="POST" action="process_reservation.php">
                        <!-- Step Progress -->
                        <div class="step-progress">
                            <div class="step-item active" id="step1Item">
                                <div class="step-circle">1</div>
                                <div class="step-title">Reservation Details</div>
                                <div class="step-subtitle">Provide information about your activity</div>
                            </div>
                            <div class="step-item" id="step2Item">
                                <div class="step-circle">2</div>
                                <div class="step-title">Date & Time</div>
                                <div class="step-subtitle">Select when you need the room</div>
                            </div>
                            <div class="step-item" id="step3Item">
                                <div class="step-circle">3</div>
                                <div class="step-title">Confirm Room</div>
                                <div class="step-subtitle">Review your room selection</div>
                            </div>
                        </div>

                        <!-- Step 1: Reservation Details -->
                        <div class="step-content active" id="step1">
                            <div class="form-group">
                                <label for="activityName" class="form-label">Activity Name</label>
                                <input type="text" id="activityName" name="activityName" class="form-control" placeholder="e.g., Group Study Session" required>
                                <div class="form-hint">Activity name must be at least 2 words</div>
                            </div>

                            <div class="form-group">
                                <label for="purpose" class="form-label">Purpose</label>
                                <textarea id="purpose" name="purpose" class="form-control" placeholder="Describe the purpose of your reservation..." required></textarea>
                                <div class="form-hint">Purpose must be at least 1 sentence</div>
                            </div>

                            <div class="form-group">
                                <label for="participants" class="form-label">Number of Participants</label>
                                <input type="number" id="participants" name="participants" class="form-control" placeholder="e.g., 10" min="1" required>
                                <div class="form-hint">Please enter a valid number of participants</div>
                                <div class="form-error" id="capacityError" style="display: none; color: #e74c3c; margin-top: 5px; font-size: 0.85em;"></div>
                            </div>

                            <div class="modal-btns">
                                <div></div> <!-- Empty div for alignment -->
                                <button type="button" class="btn-next" id="toStep2">Next</button>
                            </div>
                        </div>

                        <!-- Step 2: Date & Time -->
                        <div class="step-content" id="step2">
                            <div class="form-group">
                                <label for="reservationDate" class="form-label">Reservation Date</label>
                                <div class="date-input-container">
                                    <i class="fa fa-calendar date-input-icon"></i>
                                    <?php
                                    // Generate tomorrow's date in YYYY-MM-DD format
                                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                    ?>
                                    <input type="date" id="reservationDate" name="reservationDate" class="form-control date-input" min="<?php echo $tomorrow; ?>" value="<?php echo $tomorrow; ?>" required>
                                    <small class="form-text text-muted">You can only select tomorrow or later dates</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="startTime" class="form-label">Start Time</label>
                                <div class="time-input-container">
                                    <i class="fa fa-clock-o time-input-icon"></i>
                                    <select id="startTime" name="reservationTime" class="form-control time-input" required>
                                        <option value="">Select a start time</option>
                                        <option value="7:00">7:00 AM</option>
                                        <option value="7:30">7:30 AM</option>
                                        <option value="8:00">8:00 AM</option>
                                        <option value="8:30">8:30 AM</option>
                                        <option value="9:00">9:00 AM</option>
                                        <option value="9:30">9:30 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="10:30">10:30 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="11:30">11:30 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="12:30">12:30 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="13:30">1:30 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="14:30">2:30 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="15:30">3:30 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="16:30">4:30 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Duration</label>
                                <div class="duration-container">
                                    <div class="duration-input-group">
                                        <input type="number" id="durationHours" name="durationHours" class="form-control" min="0" max="8" value="1" required>
                                        <span class="duration-label">hours</span>
                                        <input type="number" id="durationMinutes" name="durationMinutes" class="form-control" min="0" max="59" value="30" required>
                                        <span class="duration-label">minutes</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="endTime" class="form-label">End Time</label>
                                <div class="time-input-container">
                                    <i class="fa fa-clock-o time-input-icon"></i>
                                    <input type="text" id="endTime" name="endTime" class="form-control time-input" readonly>
                                </div>
                            </div>

                            <div class="modal-btns">
                                <button type="button" class="btn-back" id="backToStep1">Back</button>
                                <button type="button" class="btn-next" id="toStep3">Next</button>
                            </div>
                        </div>

                        <!-- Step 3: Select Room -->
                        <div class="step-content" id="step3">
                            <div class="form-group">
                                <label class="form-label">Selected Room</label>
                                <div class="selected-room-info" id="selectedRoomInfo">
                                    <!-- This will be populated with room info via JavaScript -->


                                </div>
                            </div>

                            <input type="hidden" id="selectedRoom" name="roomId" required>

                            <div class="modal-btns">
                                <button type="button" class="btn-back" id="backToStep2">Back</button>
                                <button type="submit" class="btn-submit" id="submitReservation">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>