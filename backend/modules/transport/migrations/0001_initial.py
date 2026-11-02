# Item 22: Vehicle, Driver, Trip models (Module G — transport and lifts).
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
            name='Vehicle',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('vehicle_type', models.CharField(
                    choices=[('shuttle', 'Shuttle'), ('taxi', 'Taxi'), ('pickup', 'Pickup')],
                    default='shuttle', max_length=16,
                )),
                ('license_plate', models.CharField(max_length=20, unique=True)),
                ('capacity', models.PositiveIntegerField(default=4)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'transport_vehicle',
                'ordering': ['license_plate'],
            },
        ),
        migrations.CreateModel(
            name='Driver',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=200)),
                ('phone', models.CharField(blank=True, default='', max_length=50)),
                ('license_number', models.CharField(blank=True, default='', max_length=50)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'transport_driver',
                'ordering': ['name'],
            },
        ),
        migrations.CreateModel(
            name='Trip',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('starts_at', models.DateTimeField()),
                ('ends_at', models.DateTimeField(blank=True, null=True)),
                ('status', models.CharField(
                    choices=[('scheduled', 'Scheduled'), ('in_progress', 'In Progress'), ('completed', 'Completed'), ('cancelled', 'Cancelled')],
                    default='scheduled', max_length=16,
                )),
                ('passenger_count', models.PositiveIntegerField(default=0)),
                ('notes', models.CharField(blank=True, default='', max_length=255)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('driver', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='trips', to='transport.driver',
                )),
                ('from_building', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='trips_from', to='core.building',
                )),
                ('to_building', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='trips_to', to='core.building',
                )),
                ('vehicle', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='trips', to='transport.vehicle',
                )),
            ],
            options={
                'db_table': 'transport_trip',
                'ordering': ['-starts_at'],
            },
        ),
    ]
