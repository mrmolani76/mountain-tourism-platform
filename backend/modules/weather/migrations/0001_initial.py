# Item 22: WeatherLimit, WeatherWarning models (Module H — weather).
# Written for DB-per-tenant tenant DBs; applied when tenant DBs are provisioned.

import django.db.models.deletion
from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='WeatherLimit',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('limit_type', models.CharField(
                    choices=[('wind_speed', 'Wind Speed'), ('temperature', 'Temperature'), ('snowfall', 'Snowfall'), ('visibility', 'Visibility')],
                    max_length=16,
                )),
                ('threshold_value', models.DecimalField(decimal_places=2, max_digits=10)),
                ('threshold_unit', models.CharField(default='', max_length=16)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
            ],
            options={
                'db_table': 'weather_weatherlimit',
                'ordering': ['limit_type'],
            },
        ),
        migrations.CreateModel(
            name='WeatherWarning',
            fields=[
                ('id', models.BigAutoField(auto_created=True, primary_key=True, serialize=False, verbose_name='ID')),
                ('affected_area', models.CharField(max_length=200)),
                ('description', models.CharField(max_length=255)),
                ('current_value', models.DecimalField(decimal_places=2, max_digits=10)),
                ('raised_at', models.DateTimeField(auto_now_add=True)),
                ('resolved_at', models.DateTimeField(blank=True, null=True)),
                ('is_active', models.BooleanField(default=True)),
                ('created_at', models.DateTimeField(auto_now_add=True)),
                ('updated_at', models.DateTimeField(auto_now=True)),
                ('weather_limit', models.ForeignKey(
                    on_delete=django.db.models.deletion.PROTECT,
                    related_name='warnings', to='weather.weatherlimit',
                )),
            ],
            options={
                'db_table': 'weather_weatherwarning',
                'ordering': ['-raised_at'],
            },
        ),
    ]
