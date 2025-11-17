#!/bin/bash

# Deploy AI Matching Infrastructure to AWS

echo "=== Deploying AI Matching Infrastructure ==="

# Variables
STACK_NAME="pharma-ai-matching-stack"
REGION="us-east-1"
LAMBDA_FUNCTION="pharma-matching-processor"

# Step 1: Deploy CloudFormation Stack
echo "Step 1: Deploying CloudFormation stack..."
aws cloudformation deploy \
    --template-file cloudformation/ai-matching-stack.yaml \
    --stack-name $STACK_NAME \
    --capabilities CAPABILITY_NAMED_IAM \
    --region $REGION

if [ $? -ne 0 ]; then
    echo "Error deploying CloudFormation stack"
    exit 1
fi

# Step 2: Package Lambda function
echo "Step 2: Packaging Lambda function..."
cd lambda
pip install -r requirements.txt -t .
zip -r ../pharma-matching.zip .
cd ..

# Step 3: Update Lambda function code
echo "Step 3: Updating Lambda function code..."
aws lambda update-function-code \
    --function-name $LAMBDA_FUNCTION \
    --zip-file fileb://pharma-matching.zip \
    --region $REGION

if [ $? -ne 0 ]; then
    echo "Error updating Lambda function"
    exit 1
fi

# Step 4: Get outputs
echo "Step 4: Getting stack outputs..."
aws cloudformation describe-stacks \
    --stack-name $STACK_NAME \
    --region $REGION \
    --query 'Stacks[0].Outputs'

echo "=== Deployment completed successfully ==="
echo ""
echo "Next steps:"
echo "1. Update .env file with AWS credentials"
echo "2. Run database migration: php spark migrate"
echo "3. Access the AI Matching module at: /aimatching"
