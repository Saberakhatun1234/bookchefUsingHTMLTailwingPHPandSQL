<?php if ($booking['status'] === 'active'): ?>
  <a href="cancel-booking.php?id=<?= $booking['id'] ?>"
     onclick="return confirm('Are you sure you want to cancel this booking?')"
     class="text-red-600 hover:underline">Cancel</a>
<?php else: ?>
  <span class="text-gray-500">Cancelled</span>
<?php endif; ?>
