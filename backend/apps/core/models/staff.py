"""Staff record with roles + outlet access (Module A auth)."""

from django.db import models

from .base import TenantManager


class StaffRole(models.TextChoices):
    """Roles aligned with PDF §3."""
    SUPER_ADMIN = 'super_admin', 'Super Admin'
    OWNER = 'owner', 'Owner'
    MANAGER = 'manager', 'Manager'
    FRONT_DESK = 'front_desk', 'Front Desk'
    CASHIER = 'cashier', 'Cashier'
    RENTAL_DESK = 'rental_desk', 'Rental Desk'
    SERVICE_STAFF = 'service_staff', 'Service Staff'
    TRANSPORT_DESK = 'transport_desk', 'Transport Desk'
    LIFT_OPERATOR = 'lift_operator', 'Lift Operator'


class Staff(models.Model):
    email = models.EmailField(max_length=254, unique=True)
    password = models.CharField(max_length=128)  # hashed, no plaintext
    role = models.CharField(max_length=32, choices=StaffRole.choices,
                            default=StaffRole.FRONT_DESK)
    tenant = models.ForeignKey(
        'core.Tenant',
        on_delete=models.CASCADE,
        db_index=True,
        related_name='staff_set',
    )
    is_active = models.BooleanField(default=True)
    last_login = models.DateTimeField(null=True, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    objects = TenantManager()  # tenant-scoped (default)
    all_objects = models.Manager()  # unscoped, for login lookup by email

    class Meta:
        db_table = 'core_staff'
        ordering = ['email']

    def __str__(self):
        return self.email

    # Mimic Django user so DRF SessionAuthentication / force_authenticate work.
    @property
    def is_authenticated(self):
        return True

    @property
    def is_anonymous(self):
        return False

    @property
    def is_super_admin(self):
        return self.role == StaffRole.SUPER_ADMIN

    @property
    def can_reset_password(self):
        """Owner and manager can set staff passwords (no email in MVP)."""
        return self.role in (StaffRole.OWNER, StaffRole.MANAGER)

    @property
    def usable_password(self):
        """Dummy so Django auth helpers don't complain."""
        return self.password


class StaffOutletAccess(models.Model):
    """Which outlets a staff member may use (tenant-scoped)."""
    staff = models.ForeignKey(
        Staff, on_delete=models.CASCADE, related_name='outlet_accesses'
    )
    outlet = models.ForeignKey(
        'core.Outlet',
        on_delete=models.CASCADE,
        related_name='staff_accesses',
    )
    created_at = models.DateTimeField(auto_now_add=True)

    objects = TenantManager()

    class Meta:
        db_table = 'core_staff_outlet_access'
        unique_together = [['staff', 'outlet']]
        ordering = ['staff', 'outlet']
