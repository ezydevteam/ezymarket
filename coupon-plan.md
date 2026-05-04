# Coupon Discount System Implementation Plan

This plan outlines the steps to implement a robust coupon discount system that allows users to apply discount codes to their cart totals.

## User Review Required

> [!IMPORTANT]
> The coupon system will currently work at the **Cart Total** level (applying a discount to the entire order). We need to decide if we also need item-specific coupons (e.g., 10% off certain categories) in the future. This implementation focuses on the overall order discount.

> [!CAUTION]
> Applying a discount after the transaction is created but before payment must be handled carefully to ensure the payment gateways receive the correct *discounted* total.

## Proposed Changes

### Database & Models

#### [NEW] [Coupon.php](file:///d:/laragon/www/ezymarket/app/Models/Coupon.php)
Create a new `Coupon` model and migration:
- `id`, `code` (unique), `type` (fixed/percentage)
- `value` (the actual discount amount or %)
- `min_spend`, `max_spend` (optional validation)
- `usage_limit` (total times it can be used)
- `used_count` (increments on successful purchase)
- `limit_per_user` (optinal, to prevent abuse)
- `expires_at` (datetime)
- `status` (boolean Active/Inactive)

#### [MODIFY] [Transaction.php](file:///d:/laragon/www/ezymarket/app/Models/Financial/Transaction.php)
- Add `coupon_id` (foreign key) and `discount_amount` (decimal).
- Update the `Transaction` model to include these in `$fillable`.

---

### Backend Logic

#### [MODIFY] [CartController.php](file:///d:/laragon/www/ezymarket/app/Http/Controllers/Theme/CartController.php)
- Implement `applyCoupon(Request $request)`:
    - Validate the code exists and is active.
    - Check expiry and `usage_limit`.
    - Check `min_spend` against current `cartTotal`.
    - Save the coupon code as a session variable (e.g., `applied_coupon`).
- Implement `removeCoupon()`:
    - Clear the session variable.
- Update `index()`:
    - If a coupon is in the session, calculate and subtract the discount from `$cartTotal` passed to the view.
- Update `checkout()`:
    - During transaction creation, calculate the discount and store it in the `Transaction` record.
    - Ensure the final `amount` sent to gateways reflects the discount.

---

### Frontend (Blade Views)

#### [MODIFY] [cart.blade.php](file:///d:/laragon/www/ezymarket/resources/views/themes/main/cart.blade.php)
- Add an input field for the coupon code in the Order Summary sidebar.
- Show "Coupon Applied: CODE (- AMOUNT)" if one is active.
- Add a "Remove" button for the applied coupon.
- Ensure the "Total" reflects the discount.

---

### Future Enhancements (Not in this initial scope)
- Admin panel to manage coupons.
- Item-level specific coupons.
- Category-wide specific coupons.

## Verification Plan

### Automated Tests
- `php artisan test` (if existing tests for cart exist).
- I will manually test various scenarios:
    - Expired coupon should fail.
    - Percentage vs Fixed amount calculations.
    - Min spend enforcement.
    - Usage limit enforcement.

### Manual Verification
- Add items to cart.
- Apply a fixed $10 coupon.
- Verify total updates.
- Remove coupon.
- Apply a 20% coupon.
- Verify total updates.
- Proceed to checkout and ensure the created Transaction has the correct discounted value in DB.
