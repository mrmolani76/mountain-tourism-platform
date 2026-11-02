"""Report — read across modules (Module I — reports/dashboard)."""

from django.db import models


class ReportType(models.TextChoices):
    FOLIO_SUMMARY = 'folio_summary', 'Folio Summary'
    OCCUPANCY = 'occupancy', 'Occupancy'
    REVENUE = 'revenue', 'Revenue'
    RENTAL = 'rental', 'Rental'
    TRANSPORT = 'transport', 'Transport'


class Report(models.Model):
    """A generated report across modules. Stored result for dashboard viewing."""

    report_type = models.CharField(
        max_length=32,
        choices=ReportType.choices,
    )
    title = models.CharField(max_length=200)
    parameters = models.JSONField(default=dict, blank=True)
    result = models.JSONField(default=dict, blank=True)
    generated_by = models.ForeignKey(
        'core.Staff',
        on_delete=models.PROTECT,
        related_name='reports',
    )
    generated_at = models.DateTimeField(auto_now_add=True)
    is_active = models.BooleanField(default=True)

    objects = models.Manager()

    class Meta:
        db_table = 'reports_report'
        ordering = ['-generated_at']

    def __str__(self):
        return f'{self.title} ({self.report_type})'
