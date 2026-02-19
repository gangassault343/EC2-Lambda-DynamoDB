import boto3
import time

# Initialize AWS clients
ec2 = boto3.client('ec2')
dynamodb = boto3.resource('dynamodb')
table = dynamodb.Table('EC2CloudTrailLogs')

def lambda_handler(event, context):
    try:
        # Retrieve all instances across all reservations
        response = ec2.describe_instances()
        timestamp = str(int(time.time()))
        
        for reservation in response.get('Reservations', []):
            for instance in reservation.get('Instances', []):
                instance_id = instance['InstanceId']
                state = instance['State']['Name']
                
                # Write state to DynamoDB
                table.put_item(
                    Item={
                        'InstanceId': instance_id,
                        'LogTimestamp': timestamp,
                        'State': state,
                        'InstanceType': instance.get('InstanceType'),
                        'LaunchTime': str(instance.get('LaunchTime'))
                    }
                )
        
        return {'statusCode': 200, 'body': 'EC2 states logged successfully.'}
        
    except Exception as e:
        print(f"Error: {str(e)}")
        return {'statusCode': 500, 'body': str(e)}
