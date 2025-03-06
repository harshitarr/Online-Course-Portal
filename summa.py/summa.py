import pandas as pd

# Load the data
file_path = 'cleaned_ptbxl_data_final.csv'  # Update the path if needed
df = pd.read_csv(file_path)

# Define the columns to analyze
columns_to_analyze = ['age', 'height', 'weight']

# Calculate statistics for the entire columns
results = []
for col in columns_to_analyze:
    mean_value = df[col].mean()
    median_value = df[col].median()
    mode_value = df[col].mode()[0] if not df[col].mode().empty else None
    std_dev = df[col].std()
    variance = df[col].var()
    coef_variance = (std_dev / mean_value) * 100 if mean_value != 0 else None
    
    results.append({
        'Column': col,
        'Mean': round(mean_value, 2),
        'Median': round(median_value, 2),
        'Mode': round(mode_value, 2) if mode_value is not None else None,
        'Standard Deviation': round(std_dev, 2),
        'Variance': round(variance, 2),
        'Coefficient of Variance (%)': round(coef_variance, 2) if coef_variance is not None else None
    })

# Display the first few rows of the dataset
print(df.head())

# Display the computed statistics in a formatted way
print("\nStatistics Summary:\n")
for result in results:
    print(f"Column: {result['Column']}")
    for key, value in result.items():
        if key != 'Column':
            print(f"  {key}: {value}")
    print()
