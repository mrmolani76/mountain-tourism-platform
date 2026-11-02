"""DRF routes for modules.pos (Module D — POS products, orders, lines)."""

from django.urls import include, path
from rest_framework.routers import DefaultRouter

from modules.pos import views

router = DefaultRouter()
router.register(r'product-categories', views.ProductCategoryViewSet, basename='product-category')
router.register(r'products', views.ProductViewSet, basename='product')
router.register(r'orders', views.OrderViewSet, basename='order')
router.register(r'order-lines', views.OrderLineViewSet, basename='order-line')

urlpatterns = [
    path('', include(router.urls)),
]
