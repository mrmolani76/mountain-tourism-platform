"""Folio services — money operations (append-only, void only)."""

from django.core.exceptions import ValidationError
from django.db import transaction
from django.utils import timezone

from modules.core.models import Staff
from modules.folio.models import Charge, Folio, Payment
from modules.folio.permissions import IsFolioManager


def open_folio(*, guest_name: str, stay_reference: str = '', currency: str = 'EUR') -> Folio:
    """Open a new folio."""
    return Folio.objects.create(
        guest_name=guest_name,
        stay_reference=stay_reference,
        currency=currency,
        status=Folio.FolioStatus.OPEN,
    )


def close_folio(*, folio: Folio, actor: Staff, confirm_zero_balance: bool = False) -> Folio:
    """Close a folio. Requires zero balance unless confirmed."""
    if folio.status != Folio.FolioStatus.OPEN:
        raise ValidationError('Folio is not open.')
    if not actor.can_reset_password:
        # Only owner/manager can close (reuse can_reset_password as proxy for manager+)
        from rest_framework.exceptions import PermissionDenied
        raise PermissionDenied('Only owner/manager can close a folio.')
    balance = get_folio_balance(folio)
    if balance != 0 and not confirm_zero_balance:
        raise ValidationError(
            f'Folio balance is {balance}. Must be zero to close, or set confirm_zero_balance.'
        )
    folio.status = Folio.FolioStatus.CLOSED
    folio.save(update_fields=['status', 'updated_at'])
    return folio


@transaction.atomic
def post_charge(
    *,
    folio: Folio,
    outlet,
    description: str,
    amount,
    tax_amount=0,
    tax_rate=0,
    source: str,
    actor: Staff,
) -> Charge:
    """Post a charge to a folio (append-only)."""
    if folio.status != Folio.FolioStatus.OPEN:
        raise ValidationError('Cannot post charge to a non-open folio.')
    charge = Charge.objects.create(
        folio=folio,
        outlet=outlet,
        description=description,
        amount=amount,
        tax_amount=tax_amount,
        tax_rate=tax_rate,
        source=source,
    )
    folio.total_amount = folio.compute_total()
    folio.save(update_fields=['total_amount', 'updated_at'])
    return charge


@transaction.atomic
def record_payment(
    *,
    folio: Folio,
    method: str,
    amount,
    reference: str = '',
    actor: Staff,
) -> Payment:
    """Record a payment against a folio (append-only)."""
    if folio.status != Folio.FolioStatus.OPEN:
        raise ValidationError('Cannot record payment on a non-open folio.')
    payment = Payment.objects.create(
        folio=folio,
        method=method,
        amount=amount,
        reference=reference,
    )
    folio.total_amount = folio.compute_total()
    folio.save(update_fields=['total_amount', 'updated_at'])
    return payment


@transaction.atomic
def void_charge(*, charge: Charge, reason: str, voided_by: int) -> Charge:
    """Void a charge (Rule 5: void only, never edit/delete)."""
    if charge.is_voided:
        raise ValidationError('Charge already voided.')
    charge.is_voided = True
    charge.voided_at = timezone.now()
    charge.voided_reason = reason
    charge.voided_by = voided_by
    charge.save(update_fields=['is_voided', 'voided_at', 'voided_reason', 'voided_by', 'updated_at'])
    charge.folio.total_amount = charge.folio.compute_total()
    charge.folio.save(update_fields=['total_amount', 'updated_at'])
    return charge


@transaction.atomic
def void_payment(*, payment: Payment, reason: str, voided_by: int) -> Payment:
    """Void a payment (Rule 5: void only, never edit/delete)."""
    if payment.is_voided:
        raise ValidationError('Payment already voided.')
    payment.is_voided = True
    payment.voided_at = timezone.now()
    payment.voided_reason = reason
    payment.voided_by = voided_by
    payment.save(update_fields=['is_voided', 'voided_at', 'voided_reason', 'voided_by', 'updated_at'])
    payment.folio.total_amount = payment.folio.compute_total()
    payment.folio.save(update_fields=['total_amount', 'updated_at'])
    return payment


def get_folio_balance(folio: Folio):
    """Return current balance (charges - payments, excluding voided)."""
    return folio.compute_total()
