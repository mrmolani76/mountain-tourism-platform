# Item 20: BoardPlan, RoomType, Room, Stay models (Module B — hotel/folio domain).
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
        ('core', '0005_building_season_settings_outlet_config'),
        ('folio', '0001_initial'),
    ]

    operations = [
        migrations.CreateModel(
            name='BoardPlan',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=100)),
                ('code', models.CharField(max_length=16, unique=True)),
                ('description', models.CharField(blank=True, default='', max_length=255)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'hotel_board_plan',
                'ordering': ['code'],
            },
        ),
        migrations.CreateModel(
            name='RoomType',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('name', models.CharField(max_length=100)),
                ('code', models.CharField(max_length=32, unique=True)),
                ('description', models.CharField(blank=True, default='', max_length=255)),
                ('capacity', models.PositiveIntegerField(default=1)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'hotel_room_type',
                'ordering': ['code'],
            },
        ),
        migrations.CreateModel(
            name='Room',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('number', models.CharField(max_length=20)),
                ('floor', models.PositiveIntegerField(blank=True, null=True)),
                ('is_active', models.BooleanField(default=True)),
                ('is_available', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('building', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='rooms', to='core.building',
                )),
                ('room_type', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='rooms', to='hotel.roomtype',
                )),
            ],
            options={
                'db_table': 'hotel_room',
                'ordering': ['building', 'number'],
                'unique_together': {('building', 'number')},
            },
        ),
        migrations.CreateModel(
            name='Stay',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('guest_name', models.CharField(max_length=200)),
                ('check_in', models.DateField()),
                ('check_out', models.DateField()),
                ('status', models.CharField(
                    choices=[('reserved', 'Reserved'), ('checked_in', 'Checked In'), ('checked_out', 'Checked Out'), ('cancelled', 'Cancelled')],
                    default='reserved', max_length=16,
                )),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('board_plan', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='stays', to='hotel.boardplan',
                )),
                ('folio', models.ForeignKey(
                    blank=True, null=True,
                    on_delete=django.db.models.deletion.SET_NULL,
                    related_name='stays', to='folio.folio',
                )),
                ('room', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='stays', to='hotel.room',
                )),
            ],
            options={
                'db_table': 'hotel_stay',
                'ordering': ['-check_in'],
            },
        ),
    ]
