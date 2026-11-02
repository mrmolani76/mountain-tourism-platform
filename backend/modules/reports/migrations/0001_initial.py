# Item 22: Report model (Module I — dashboard and reports).
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
        ('core', '0005_building_season_settings_outlet_config'),
    ]

    operations = [
        migrations.CreateModel(
            name='Report',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('report_type', models.CharField(
                    choices=[('folio_summary', 'Folio Summary'), ('occupancy', 'Occupancy'), ('revenue', 'Revenue'), ('rental', 'Rental'), ('transport', 'Transport')],
                    max_length=32,
                )),
                ('title', models.CharField(max_length=200)),
                ('parameters', models.JSONField(default=dict, blank=True)),
                ('result', models.JSONField(default=dict, blank=True)),
                ('generated_at', models.DateTimeField(auto_now_add=True)),
                ('is_active', models.BooleanField(default=True)),
                ('generated_by', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='reports', to='core.staff',
                )),
            ],
            options={
                'db_table': 'reports_report',
                'ordering': ['-generated_at'],
            },
        ),
    ]
