import React, { useState } from 'react';

/**
 * 星評価コンポーネント
 * @param {number} value - 現在の評価値（1-5）
 * @param {function} onChange - 評価変更時のコールバック
 * @param {boolean} readOnly - 読み取り専用モード
 * @param {number} size - 星のサイズ（px）
 */
export default function StarRating({ value = 0, onChange = null, readOnly = false, size = 20 }) {
    const [hoverValue, setHoverValue] = useState(0);

    const handleClick = (rating) => {
        if (!readOnly && onChange) {
            onChange(rating);
        }
    };

    const handleMouseEnter = (rating) => {
        if (!readOnly) {
            setHoverValue(rating);
        }
    };

    const handleMouseLeave = () => {
        if (!readOnly) {
            setHoverValue(0);
        }
    };

    const displayValue = hoverValue || value;

    return (
        <div 
            className={`star-rating ${readOnly ? '' : 'star-rating-interactive'}`}
            style={{ display: 'inline-flex', gap: '2px' }}
        >
            {[1, 2, 3, 4, 5].map((rating) => {
                const isFilled = rating <= displayValue;
                return (
                    <span
                        key={rating}
                        className={`star ${isFilled ? 'star-filled' : 'star-empty'}`}
                        style={{
                            fontSize: `${size}px`,
                            cursor: readOnly ? 'default' : 'pointer',
                            color: isFilled ? '#ffc107' : '#e0e0e0',
                            transition: 'color 0.2s ease',
                        }}
                        onClick={() => handleClick(rating)}
                        onMouseEnter={() => handleMouseEnter(rating)}
                        onMouseLeave={handleMouseLeave}
                    >
                        {isFilled ? '★' : '☆'}
                    </span>
                );
            })}
            {!readOnly && (
                <span style={{ marginLeft: '8px', fontSize: '14px', color: '#666' }}>
                    {displayValue > 0 ? `${displayValue}.0` : '評価してください'}
                </span>
            )}
        </div>
    );
}
