"""Domain and API exception types shared across modules."""


class DomainError(Exception):
    """Base class for domain-layer errors."""

    def __init__(self, message, code='domain_error', details=None):
        super().__init__(message)
        self.message = message
        self.code = code
        self.details = details or {}


class MoneyAppendOnlyError(DomainError):
    """Raised when an attempt is made to edit or delete a charge/payment."""

    def __init__(self, message='Charge or payment is append-only. Void instead of editing.'):
        super().__init__(message, code='money_append_only')


class TenantIsolationError(DomainError):
    """Raised when cross-tenant data access is attempted."""

    def __init__(self, message='Cross-tenant data access is not allowed.'):
        super().__init__(message, code='tenant_isolation')


class CapacityConflictError(DomainError):
    """Raised when a slot/room/item is already booked for the requested time."""

    def __init__(self, message='Capacity conflict: resource already booked for this slot.'):
        super().__init__(message, code='capacity_conflict')

