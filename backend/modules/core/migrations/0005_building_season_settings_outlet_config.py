# Item 06: Building, Season, TenantSettings models + Outlet building/type/updated_at.
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('core', '0004_db_per_tenant_drop_tenant_fk'),
    ]

    operations = [
        migrations.CreateModel(
            name='Building',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=200)),
                ('building_type', models.CharField(
                    choices=[
                        ('valley_station', 'Valley Station'),
                        ('main_hotel', 'Main Hotel'),
                        ('chalets', 'Chalets'),
                        ('mid_station', 'Mid Station'),
                        ('mountain_hut', 'Mountain Hut'),
                        ('summit_station', 'Summit Station'),
                        ('other', 'Other'),
                    ],
                    default='other',
                    max_length=32,
                )),
                ('altitude_m', models.IntegerField(blank=True, null=True)),
                ('latitude', models.DecimalField(blank=True, decimal_places=6, max_digits=9, null=True)),
                ('longitude', models.DecimalField(blank=True, decimal_places=6, max_digits=9, null=True)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'core_building',
                'ordering': ['altitude_m', 'name'],
            },
        ),
        migrations.CreateModel(
            name='Season',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('kind', models.CharField(
                    choices=[
                        ('winter', 'Winter'),
                        ('summer', 'Summer'),
                        ('closed', 'Closed'),
                    ],
                    max_length=16,
                )),
                ('starts_on', models.DateField()),
                ('ends_on', models.DateField()),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'core_season',
                'ordering': ['starts_on'],
            },
        ),
        migrations.CreateModel(
            name='TenantSettings',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('currency', models.CharField(default='EUR', max_length=8)),
                ('tax_rates', models.JSONField(blank=True, default=list)),
                ('timezone', models.CharField(default='UTC', max_length=64)),
                ('units', models.CharField(
                    choices=[('metric', 'Metric'), ('imperial', 'Imperial')],
                    default='metric',
                    max_length=16,
                )),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'core_tenant_settings',
            },
        ),
        migrations.AddField(
            model_name='outlet',
            name='building',
            field=models.ForeignKey(
                blank=True,
                null=True,
                on_delete=django.db.models.deletion.PROTECT,
                related_name='outlets',
                to='core.building',
            ),
        ),
        migrations.AddField(
            model_name='outlet',
            name='outlet_type',
            field=models.CharField(
                choices=[
                    ('restaurant', 'Restaurant'),
                    ('self_service', 'Self Service'),
                    ('hut_kitchen', 'Hut Kitchen'),
                    ('panorama_restaurant', 'Panorama Restaurant'),
                    ('shop', 'Shop'),
                    ('rental', 'Rental'),
                    ('spa', 'Spa'),
                    ('sauna', 'Sauna'),
                    ('gym', 'Gym'),
                    ('ski_school', 'Ski School'),
                    ('reception', 'Reception'),
                    ('tickets', 'Tickets'),
                    ('sports', 'Sports'),
                    ('transport', 'Transport'),
                    ('lifts', 'Lifts'),
                    ('other', 'Other'),
                ],
                default='other',
                max_length=32,
            ),
        ),
        migrations.AddField(
            model_name='outlet',
            name='updated_at',
            field=models.DateTimeField(auto_now=True, null=True),
        ),
        migrations.AlterField(
            model_name='outlet',
            name='building',
            field=models.ForeignKey(
                on_delete=django.db.models.deletion.PROTECT,
                related_name='outlets',
                to='core.building',
            ),
        ),
        migrations.AlterField(
            model_name='outlet',
            name='updated_at',
            field=models.DateTimeField(auto_now=True),
        ),
    ]
