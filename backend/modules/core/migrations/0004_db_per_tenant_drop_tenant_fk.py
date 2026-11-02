# DB-per-tenant: drop shared-DB tenant FK / Tenant model from core.
# Code only in this work item — migration file written, not applied.

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('core', '0003_staff_role_outlet_staffoutletaccess'),
    ]

    operations = [
        migrations.AlterField(
            model_name='staff',
            name='role',
            field=models.CharField(
                choices=[
                    ('owner', 'Owner'),
                    ('manager', 'Manager'),
                    ('front_desk', 'Front Desk'),
                    ('cashier', 'Cashier'),
                    ('rental_desk', 'Rental Desk'),
                    ('service_staff', 'Service Staff'),
                    ('transport_desk', 'Transport Desk'),
                    ('lift_operator', 'Lift Operator'),
                ],
                default='front_desk',
                max_length=32,
            ),
        ),
        migrations.RemoveField(
            model_name='staff',
            name='tenant',
        ),
        migrations.RemoveField(
            model_name='outlet',
            name='tenant',
        ),
        migrations.DeleteModel(
            name='Tenant',
        ),
    ]
