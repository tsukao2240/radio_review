import React, { useState, useEffect } from 'react';

/**
 * タグ選択コンポーネント
 * @param {Array} availableTags - 選択可能なタグリスト [{id, name}, ...]
 * @param {Array} selectedTags - 選択済みタグIDの配列
 * @param {function} onChange - タグ選択変更時のコールバック
 * @param {boolean} readOnly - 読み取り専用モード
 */
export default function TagSelector({ availableTags = [], selectedTags = [], onChange = null, readOnly = false }) {
    const [selected, setSelected] = useState(selectedTags);

    useEffect(() => {
        setSelected(selectedTags);
    }, [selectedTags]);

    const handleToggle = (tagId) => {
        if (readOnly) return;

        let newSelected;
        if (selected.includes(tagId)) {
            newSelected = selected.filter(id => id !== tagId);
        } else {
            newSelected = [...selected, tagId];
        }
        
        setSelected(newSelected);
        if (onChange) {
            onChange(newSelected);
        }
    };

    return (
        <div className="tag-selector">
            {availableTags.map((tag) => {
                const isSelected = selected.includes(tag.id);
                return (
                    <button
                        key={tag.id}
                        type="button"
                        className={`btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-secondary'} me-2 mb-2`}
                        onClick={() => handleToggle(tag.id)}
                        disabled={readOnly}
                        style={{
                            cursor: readOnly ? 'default' : 'pointer',
                            opacity: readOnly ? 0.7 : 1,
                        }}
                    >
                        {isSelected && '✓ '}
                        {tag.name}
                    </button>
                );
            })}
            {!readOnly && selected.length === 0 && (
                <small className="text-muted d-block mt-1">
                    タグを選択してください（複数選択可）
                </small>
            )}
        </div>
    );
}
