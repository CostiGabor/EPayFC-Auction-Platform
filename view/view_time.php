<?php require_once "utils/AppTime.php"; ?>
<div class="bg-app-footer d-flex p-1">
    <p class="text-white mb-0"><i class="bi bi-clock "></i> <?= AppTime::get_current_datetime() ?></p>
    <div class="d-flex ms-auto mb-0">      
        <form method="post" action="time/advance/" style="display: inline;" class="">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="hour">
            <button type="submit" class="badge bg-warning-transparent text-warning border-0 mx-1 p-2"><i class="bi bi-clock "></i> +1h</button>
        </form>
    
        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="day">
            <button type="submit" class="badge bg-warning-transparent text-warning border-0 p-2"><i class="bi bi-calendar2-event"></i> +1d</button>
        </form>

        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="7">
            <input type="hidden" name="unit" value="day">
            <button type="submit" class="badge bg-warning-transparent text-warning border-0 mx-1 p-2"><i class="bi bi-calendar3-week"></i> +1w</button>
        </form>
    
        <form method="post" action="time/reset" style="display: inline;">
            <button type="submit" class="badge bg-danger-transparent text-danger border-0 p-2"><i class="bi bi-arrow-clockwise"></i> Reset</button>
        </form>       
    </div>
</div>