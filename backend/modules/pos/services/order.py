"""POS services — order operations."""

from django.core.exceptions import ValidationError

from modules.core.models import Staff
from modules.pos.models import Order, OrderLine


def place_order(*, outlet, staff: Staff, folio=None) -> Order:
    """Create a new open order at an outlet."""
    return Order.objects.create(
        outlet=outlet,
        staff=staff,
        folio=folio,
        status=Order.OrderStatus.OPEN,
    )


def add_order_line(*, order: Order, product, quantity: int, unit_price, tax_amount=0) -> OrderLine:
    """Add a line item to an open order."""
    if order.status != Order.OrderStatus.OPEN:
        raise ValidationError('Cannot add line to a non-open order.')
    line = OrderLine.objects.create(
        order=order,
        product=product,
        quantity=quantity,
        unit_price=unit_price,
        tax_amount=tax_amount,
    )
    order.total_amount = sum(
        line.line_total for line in order.lines.filter(is_voided=False)
    )
    order.save(update_fields=['total_amount', 'updated_at'])
    return line
