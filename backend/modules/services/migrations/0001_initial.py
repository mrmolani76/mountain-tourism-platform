# Item 22: Service, Slot models (Module F — hotel services and activities).
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
            name='Service',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=200)),
                ('description', models.CharField(blank=True, default='', max_length=255)),
                ('duration_minutes', models.PositiveIntegerField(default=60)),
                ('price', models.DecimalField(decimal_places=2, default=0, max_digits=10)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('outlet', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='services', to='core.outlet',
                )),
            ],
            options={
                'db_table': 'services_service',
                'ordering': ['outlet', 'name'],
            },
        ),
        migrations.CreateModel(
            name='Slot',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('starts_at', models.DateTimeField()),
                ('ends_at', models.DateTimeField()),
                ('capacity', models.PositiveIntegerField(default=1)),
                ('booked_count', models.PositiveIntegerField(default=0)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('service', models.ForeignKey(
                    on_delete=django.db.models.deletion.CASCADE,
                    related_name='slots', to='services.service',
                )),
                ('staff', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='service_slots', to='core.staff',
                )),
            ],
            options={
                'db_table': 'services_slot',
                'ordering': ['starts_at'],
            },
        ),
    ]
