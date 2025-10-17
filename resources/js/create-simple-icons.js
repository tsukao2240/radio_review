/**
 * シンプルなPWAアイコン生成スクリプト
 * テキストベースの単色アイコンを生成
 */

import sharp from 'sharp';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// アイコンサイズの定義
const ICON_SIZES = [72, 96, 128, 144, 152, 192, 384, 512];

// パス設定
const OUTPUT_DIR = path.join(__dirname, '../../public/images/icons');

// 出力ディレクトリが存在しない場合は作成
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

/**
 * SVGからPNGアイコンを生成
 */
async function generateIcon(size) {
  const outputPath = path.join(OUTPUT_DIR, `icon-${size}x${size}.png`);

  // ラジオ波をイメージしたSVGアイコン
  const svg = `
    <svg width="${size}" height="${size}" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
          <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
        </linearGradient>
      </defs>

      <!-- 背景 -->
      <rect width="${size}" height="${size}" rx="${size * 0.15}" fill="url(#grad)"/>

      <!-- ラジオ波のアイコン -->
      <g transform="translate(${size/2}, ${size/2})">
        <!-- 中心の円 -->
        <circle cx="0" cy="0" r="${size * 0.08}" fill="white"/>

        <!-- 内側の波 -->
        <path d="M ${-size * 0.15} ${-size * 0.15} Q ${-size * 0.18} 0 ${-size * 0.15} ${size * 0.15}"
              stroke="white" stroke-width="${size * 0.04}" fill="none" stroke-linecap="round"/>
        <path d="M ${size * 0.15} ${-size * 0.15} Q ${size * 0.18} 0 ${size * 0.15} ${size * 0.15}"
              stroke="white" stroke-width="${size * 0.04}" fill="none" stroke-linecap="round"/>

        <!-- 外側の波 -->
        <path d="M ${-size * 0.25} ${-size * 0.25} Q ${-size * 0.3} 0 ${-size * 0.25} ${size * 0.25}"
              stroke="white" stroke-width="${size * 0.03}" fill="none" stroke-linecap="round" opacity="0.7"/>
        <path d="M ${size * 0.25} ${-size * 0.25} Q ${size * 0.3} 0 ${size * 0.25} ${size * 0.25}"
              stroke="white" stroke-width="${size * 0.03}" fill="none" stroke-linecap="round" opacity="0.7"/>
      </g>
    </svg>
  `;

  try {
    await sharp(Buffer.from(svg))
      .png()
      .toFile(outputPath);

    console.log(`✓ Generated: icon-${size}x${size}.png`);
  } catch (error) {
    console.error(`✗ Failed to generate icon-${size}x${size}.png:`, error.message);
  }
}

/**
 * すべてのアイコンを生成
 */
async function generateAllIcons() {
  console.log('PWA Icon Generation (Simple Icons)');
  console.log('===================================\n');
  console.log(`Output: ${OUTPUT_DIR}\n`);

  // 各サイズのアイコンを生成
  for (const size of ICON_SIZES) {
    await generateIcon(size);
  }

  console.log('\n✓ All icons generated successfully!');
  console.log(`\nGenerated ${ICON_SIZES.length} icons in: ${OUTPUT_DIR}`);
}

// スクリプト実行
generateAllIcons().catch(error => {
  console.error('✗ Fatal error:', error);
  process.exit(1);
});
