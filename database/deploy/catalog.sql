SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: simgevip_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `campaign_product`
--

DROP TABLE IF EXISTS `campaign_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_product` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_product_campaign_id_foreign` (`campaign_id`),
  KEY `campaign_product_product_id_foreign` (`product_id`),
  CONSTRAINT `campaign_product_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaign_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_product`
--

LOCK TABLES `campaign_product` WRITE;
/*!40000 ALTER TABLE `campaign_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `nth_item` int(10) unsigned DEFAULT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaigns_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `show_in_nav` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (4,NULL,'İndirimli Ürünler','indirimli-urunler','SimgeVIP kalitesini ayrıcalıklı fırsatlar ve cazip sezon indirimleriyle keşfedin.','categories/qmZ93QuJhriuPNmHv1q0P0Bj1mJSal-metaaW5kaXJpbS5wbmc=-.png',1,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(5,NULL,'Yeni Gelenler','yeni-gelenler','Atölyemizden yeni çıkan, bu haftanın en taze tasarımları ve trend öncüsü parçaları.','categories/rYMgB2ol9gi2VSjtKyxLKpMRQOdhC2-metaeWVuaWdlbGVuLnBuZw==-.png',2,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(6,NULL,'Üst Giyim','ust-giyim','Gömlekten trikoya, her kombinasyonun merkezinde yer alan kaliteli dokular ve modern formlar.','categories/xELY1jt2AaeqdVJ3rePfvIjhp2Qb9h-metaw7xzdGdpeWltLnBuZw==-.png',7,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(7,6,'Sweat','ust-giyim-sweat','Dinamik şehir yaşamına eşlik eden konforlu ve pamuklu sweatshirtler.',NULL,1,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(8,6,'Gömlek','ust-giyim-gomlek','Net yakalar, akıcı kumaşlar ve kurumsal sadelik vadeden gömlek ve bluz modelleri.',NULL,2,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(9,6,'Kazak','ust-giyim-kazak','Yumuşak dokunuşlu yün ve pamuk karışımlı sıcacık triko ve kazak tasarımları.',NULL,3,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(10,6,'Hırka','ust-giyim-hirka','Mevsim geçişlerinin kurtarıcısı, zarif düğme ve örgü detaylı hırkalar.',NULL,4,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(11,6,'Tunik','ust-giyim-tunik','Rahat kalıplar ve uzun siluetlerle gün boyu zarif döküm sağlayan tunikler.',NULL,5,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(12,6,'İçlik','ust-giyim-iclik','Pürüzsüz tene uyum sağlayan, dikişsiz ve konforlu temel iç giyim parçaları.',NULL,6,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(13,NULL,'Alt Giyim','alt-giyim','Kusursuz kalıp ve yüksek kaliteli kumaşlarla her adımda özgüven veren tasarımlar.','categories/36RdyOKdgupC1Lz9uNCZmeXzgXeYRL-metaYWx0Z2l5aW0ucG5n-.png',8,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(14,13,'Etek','alt-giyim-etek','Midi boydan pileliye, feminen hareket kazandıran akıcı etek modelleri.',NULL,1,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(15,13,'Pantolon','alt-giyim-pantolon','Yüksek bel, palazzo, havuç ve klasik kesimlerle vücut proporsiyonunu yücelten pantolonlar.',NULL,2,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(16,13,'Jean','alt-giyim-jean','Kaliteli denim dokusu ve modern paça formlarıyla zamansız jeanler.',NULL,3,1,'2026-09-10 11:11:43','2026-10-04 05:47:24'),(17,NULL,'Dış Giyim','dis-giyim','İlk izlenimi belirleyen, güçlü omuz hatları ve koruyucu lüks dokularla tasarlanmış dış giyim parçaları.','categories/RFglXpVN1gl2Y0TQVjzqktOdBOuBmW-metaZMSxxZ9naXlpbS5wbmc=-.png',6,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(18,17,'Yelek','dis-giyim-yelek','Kombinlere derinlik ve modern katmanlar kazandıran tasarım yelekler.',NULL,1,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(19,17,'Mont','dis-giyim-mont','Hafif dolgular, rüzgar geçirmez yapılar ve modern hacimli montlar.',NULL,2,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(20,17,'Kap','dis-giyim-kap','Zarif boy uzunlukları ve koruyucu hafif kumaşlarla stilinizi tamamlayan kaplar.',NULL,3,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(21,17,'Ceket','dis-giyim-ceket','Ofis ve davet stilinin mimarı, kusursuz kesimli blazer ceketler.',NULL,4,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(22,17,'Trençkot','dis-giyim-trenckot','Zamansız İngiliz yakalar, su itici kumaşlar ve her mevsimin vazgeçilmezi trençkotlar.',NULL,5,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(23,17,'Kaban','dis-giyim-kaban','Soğuk günlerde asaleti koruyan kaşe ve yün karışımlı lüks kabanlar.',NULL,6,1,'2026-09-10 11:11:43','2026-09-10 19:52:17'),(24,NULL,'İkili Takım','ikili-takim','Uyumlu siluetler ve rafine detaylarla günün her anına hitap eden modern ikili kombinler.','categories/HtIT0tOanLuLVhCeFpWxgHJcACziQR-metaaWtpbGl0YWtpbS5wbmc=-.png',9,1,'2026-09-10 11:11:43','2026-10-04 05:47:20'),(25,24,'Etekli Takım','ikili-takim-etekli-takim','Klasik zarafeti modern çizgiyle buluşturan etekli takım modelleri.',NULL,1,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(26,24,'Pantolonlu Takım','ikili-takim-pantolonlu-takim','Ofis stilinden davetlere güçlü ve dengeli duruş sunan pantolonlu takımlar.',NULL,2,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(27,NULL,'Üçlü Takım','uclu-takim','Ceket, üst ve alt parçaların kusursuz dengesiyle hazırlanan prestijli gardırop yatırımları.','categories/vApm7YbmZvafWf9SKqKxwctNz6Sj91-metaw7zDp2zDvHRha8SxbS5wbmc=-.png',10,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(28,27,'Pantolonlu Takım','uclu-takim-pantolonlu-takim','İç bluz, pantolon ve tamamlayıcı ceketle stil sahibi bütünlük.',NULL,1,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(29,27,'Etekli Takım','uclu-takim-etekli-takim','Zarif etek ve tamamlayıcı üst parçalarla sofistike stil yorumu.',NULL,2,1,'2026-09-10 11:11:43','2026-09-10 12:22:11'),(30,NULL,'Elbise','elbise','Gündüzden geceye zahmetsiz şıklık sunan dökümlü kumaşlar, zarif detaylar ve feminen kesimler.','categories/HzwknMLniesI9xv2lX5miP7tX2iCU3-metaZWxiaXNlbGVyLnBuZw==-.png',4,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(31,NULL,'Şal & Eşarp','sal','İpek tuşe, nefes alan pamuk ve medine ipeği dokularıyla kombininizi taçlandıran aksesuarlar.','categories/CzzEyHucf6rJP5FGw3HctPFDjZvQM4-metaxZ9hbCBlxZ9hcnAucG5n-.png',5,1,'2026-09-10 11:11:43','2026-09-12 22:53:50'),(32,NULL,'Yeni Sezon','yeni-sezon','Yeni sezonun öne çıkan en seçkin parçaları, taze renkler ve modern siluetler.','categories/AaBxyujQi0gP20Kwj2fjPJUhGKQQSI-metaeWVuaXNlem9uLnBuZw==-.png',3,1,'2026-09-10 19:52:17','2026-09-12 22:53:50');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES (1,'Kargom ne zaman ulaşır?','Siparişiniz kargoya verildikten sonra ortalama 2-3 iş günü içinde adresinize teslim edilmektedir.',1,1,'2026-10-01 04:43:41','2026-10-01 04:43:41'),(2,'İade ve değişim yapabilir miyim?','Evet, teslim aldıktan sonra 14 gün içinde faturasıyla birlikte ürünleri kullanılmamış olarak iade edebilir veya değiştirebilirsiniz.',2,1,'2026-10-01 04:43:41','2026-10-01 04:43:41'),(3,'Hangi kargo şirketleri ile çalışıyorsunuz?','Yurtiçi Kargo ve Aras Kargo ile tüm Türkiye\'ye gönderim yapıyoruz.',3,1,'2026-10-01 04:43:41','2026-10-01 04:43:41');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (2,'İade ve Değişim','iade-ve-degisim','İade ve Değişim sayfası içeriği.',1,'2026-10-01 04:39:39','2026-10-01 04:39:39'),(3,'Gizlilik Politikası','gizlilik-politikasi','Gizlilik Politikası içeriği.',1,'2026-10-01 04:39:39','2026-10-01 04:39:39'),(4,'Mesafeli Satış Sözleşmesi','mesafeli-satis-sozlesmesi','Mesafeli Satış Sözleşmesi içeriği.',1,'2026-10-01 04:39:39','2026-10-01 04:39:39'),(5,'Kullanım Koşulları','kullanim-kosullari','<p>Simge Vip Giyim kullanım koşulları aşağıda yer almaktadır. Sitemizi kullanarak bu koşulları kabul etmiş sayılırsınız.</p><ul><li>Sitemizdeki ürün bilgileri ve fiyatları önceden haber verilmeksizin değiştirilebilir.</li><li>Siparişlerin onaylanması, stok durumu ve ödeme onayına bağlıdır.</li><li>Müşteri, verdiği bilgilerin doğruluğundan sorumludur.</li></ul>',1,'2026-10-01 06:16:31','2026-10-01 06:16:31'),(6,'Çerez Politikası','cerez-politikasi','<p>Simge Vip Giyim olarak, sitemizden en iyi şekilde faydalanabilmeniz için çerezler kullanıyoruz. Sitemizi kullanarak çerez kullanımını kabul etmiş olursunuz.</p><p>Çerezler, deneyiminizi iyileştirmek, site performansını ölçmek ve size uygun içerikler sunmak için kullanılır.</p>',1,'2026-10-01 06:16:31','2026-10-01 06:16:31'),(7,'KVKK Aydınlatma Metni','kvkk','<p><strong>Kişisel Verilerin Korunması Kanunu (KVKK) Aydınlatma Metni</strong></p><p>Simge Vip Giyim olarak kişisel verilerinizin güvenliğine önem veriyoruz. 6698 sayılı Kişisel Verilerin Korunması Kanunu uyarınca, bizimle paylaştığınız kişisel verileriniz veri sorumlusu sıfatıyla tarafımızca kaydedilecek, saklanacak, güncellenecek ve kanunun izin verdiği şartlarda işlenebilecektir.</p><p>Amacımız; sizlere daha iyi bir hizmet sunabilmek ve alışveriş süreçlerinizi sorunsuz yönetebilmektir.</p>',1,'2026-10-01 06:21:08','2026-10-01 06:21:08');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_attribute_fields`
--

DROP TABLE IF EXISTS `product_attribute_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_attribute_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(80) NOT NULL,
  `label` varchar(120) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_attribute_fields_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_attribute_fields`
--

LOCK TABLES `product_attribute_fields` WRITE;
/*!40000 ALTER TABLE `product_attribute_fields` DISABLE KEYS */;
INSERT INTO `product_attribute_fields` VALUES (1,'pattern','Ürün Kalıbı',10,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(2,'thickness','Ürün Kalınlığı',20,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(3,'length','Ürün Boyu',30,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(4,'fabric_content','Kumaş İçeriği',40,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(5,'fabric_type','Kumaş Tipi',50,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(6,'lining','Astar Durumu',60,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(7,'collar_type','Yaka Tipi',70,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(8,'sleeve_type','Kol Tipi',80,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(9,'sleeve_length','Kol Boyu',90,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(10,'closure_type','Kapama Şekli',100,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(11,'pocket','Cep',110,'2026-09-30 08:26:13','2026-09-30 08:26:13'),(12,'season','Ürün Sezonu',120,'2026-09-30 08:26:13','2026-09-30 08:26:13');
/*!40000 ALTER TABLE `product_attribute_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sizes`
--

DROP TABLE IF EXISTS `product_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_sizes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_sizes_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sizes`
--

LOCK TABLES `product_sizes` WRITE;
/*!40000 ALTER TABLE `product_sizes` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `color` varchar(255) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`sizes`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_new_season` tinyint(1) NOT NULL DEFAULT 0,
  `has_campaign` tinyint(1) NOT NULL DEFAULT 0,
  `campaign_discount_percentage` decimal(5,2) DEFAULT NULL,
  `campaign_nth_item` int(10) unsigned DEFAULT NULL,
  `pattern` varchar(255) DEFAULT NULL,
  `thickness` varchar(255) DEFAULT NULL,
  `length` varchar(255) DEFAULT NULL,
  `fabric_content` varchar(255) DEFAULT NULL,
  `fabric_type` varchar(255) DEFAULT NULL,
  `lining` varchar(255) DEFAULT NULL,
  `collar_type` varchar(255) DEFAULT NULL,
  `sleeve_type` varchar(255) DEFAULT NULL,
  `sleeve_length` varchar(255) DEFAULT NULL,
  `closure_type` varchar(255) DEFAULT NULL,
  `pocket` varchar(255) DEFAULT NULL,
  `season` varchar(255) DEFAULT NULL,
  `attribute_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attribute_values`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) DEFAULT NULL,
  `meta_title` varchar(120) DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `og_image_path` varchar(255) DEFAULT NULL,
  `contact_info` text DEFAULT NULL,
  `social_media` text DEFAULT NULL,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `maintenance_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `mail_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mail_settings`)),
  `whatsapp_number` varchar(255) DEFAULT NULL,
  `announcements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`announcements`)),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'Simge Vip Giyim',NULL,NULL,NULL,NULL,'[{\"branch_name\":\"Merkez\",\"phone\":\"05321552158\",\"email\":\"deneme@deneme.com\",\"address\":\"Konya\",\"map_embed\":null}]','[{\"platform\":\"pinterest\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"},{\"platform\":\"instagram\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"},{\"platform\":\"facebook\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"},{\"platform\":\"whatsapp\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"},{\"platform\":\"youtube\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"},{\"platform\":\"twitter\",\"url\":\"http:\\/\\/localhost\\/simgevip\\/public\\/\"}]',0,'Site şu anda bakım aşamasında. Kısa süre içinde tekrar hizmet vermeye başlayacağız.','2026-10-01 05:45:45','2026-11-12 18:48:54',NULL,NULL,'{\"is_enabled\":true,\"speed\":\"normal\",\"items\":[{\"badge\":\"KAMPANYA\",\"text\":\"1500 TL ve \\u00dczeri Al\\u0131\\u015fveri\\u015flerde Kargo \\u00dccretsiz!\",\"link\":\"\\/koleksiyon\",\"is_active\":true},{\"badge\":\"YEN\\u0130 SEZON\",\"text\":\"Bahar & Yaz Koleksiyonu Ma\\u011fazam\\u0131zda ve Online Sat\\u0131\\u015fta!\",\"link\":\"\\/koleksiyon\",\"is_active\":true}]}');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sliders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sliders`
--

LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;
/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-13  1:44:38
SET FOREIGN_KEY_CHECKS=1;
