package CampusMap;

//class that stores position and radius of a sphere
//divided into world- and modelspace to work well with 3Dmodels
//provides methods to test this sphere with other spheres, rays and planes for intersection
//also provides methods to scale and move the sphere in worldspace/modelspace

public class CollisionSphere {

	private FVector m_vPositionModelSpace;
	private	FVector m_vPositionWorldSpace;
	private	float	m_fRadius;
	
	public CollisionSphere(FVector vPosition, float fRadius) {
		m_vPositionModelSpace	= new FVector(vPosition);
		m_vPositionWorldSpace	= new FVector(3);
		m_fRadius				= fRadius;
	}
	
	public CollisionSphere(float fXPos, float fYPos, float fZPos, float fRadius) {
		m_vPositionModelSpace	= new FVector(fXPos, fYPos, fZPos);
		m_vPositionWorldSpace	= new FVector(3);
		m_fRadius				= fRadius;
	}
	
	public float getRadius() {
		return m_fRadius;
	}
		
	public void setRadius(float newRadius) {
		m_fRadius = newRadius;
	}
	
	public FVector getPosition() {
		return m_vPositionWorldSpace.add(m_vPositionModelSpace);
	}
	
	public void moveAbout(FVector about) {
		if (about.getLength() == 3)
			m_vPositionWorldSpace.addMe(about);
	}
	
	public void moveTo(FVector to) {
		if (to.getLength() == 3)
			m_vPositionWorldSpace.set(to);
	}
	
	public void scaleAbout(FVector scaleValue) {
		m_fRadius *= scaleValue.magnitude();
		m_vPositionModelSpace.e[0] *= scaleValue.e[0];
		m_vPositionModelSpace.e[1] *= scaleValue.e[1];
		m_vPositionModelSpace.e[2] *= scaleValue.e[2];
	}
	
	public void moveModelSpaceTo(FVector to) {
		if (to.getLength() == 3)
			m_vPositionModelSpace.set(to);
	}
	
	public void moveModelSpaceAbout(FVector about) {
		if (about.getLength() == 3)
			m_vPositionModelSpace.add(about);
	}
	
	public void rotateModelSpaceXYZ(FVector rotation) {
		m_vPositionModelSpace.rotateMeXYZ(rotation);
	}
	
	public boolean testRay(FVector rayStart, FVector rayDir)
	{
		FVector	lineToSphere;
		float	s, t, q,
				l2, 
				r2,				
				m2,
				len;

		rayDir.normalizeMe();

		lineToSphere = getPosition().subtract(rayStart);

		s  = lineToSphere.dotProduct(rayDir);
		l2 = lineToSphere.magnitudeSqr();
		r2 = m_fRadius * m_fRadius;

		if(l2 <= r2)
		{
			// Ray starts inside or on the sphere
			return true;
		}

		if(s < 0.0f)
		{
			// Ray heading in wrong direction
			return false;
		}

		m2 = l2 - (s*s);
		if(m2 > r2)
		{
			// Ray will never hit the sphere
			return false;
		}

		q = (float)(Math.sqrt(r2 - m2));
		t = s - q;

		len = (float)(Math.sqrt(l2));
		if(t > len)
		{
			// Ray hits beyond the far point of the line
			return false;
		}

		return true;
	}
	
	public boolean testPoint(FVector point)
	{
		FVector lineToSphere = getPosition().subtract(point);
		if (lineToSphere.magnitudeSqr() > m_fRadius*m_fRadius)
			return true;
		return false;
	}
	
	public CollisionSphere testPoint(FVector point, float distance)
	{
		FVector lineToSphere = getPosition().subtract(point);
		if (lineToSphere.magnitudeSqr() < (m_fRadius+distance)*(m_fRadius+distance))
			return this;
		return null;
	}
	
	public FVector getTangent(FVector point) {
		FVector lineToSphere = getPosition().subtract(point).normalize();
		return lineToSphere.crossProduct(new FVector(0,0,1));
	}
	
	public void debugDraw(CampusMap applet) {
		FVector pos = getPosition();
		applet.fill(255, 100, 100, 255);
		applet.pushMatrix();
		applet.translate(pos.getX(), pos.getY(), pos.getZ());
		applet.sphere(m_fRadius);
		applet.popMatrix();
	}
	
	public void debugDraw(CampusMap applet, int color) {
		FVector pos = getPosition();
		applet.fill(color);
		applet.pushMatrix();
		applet.translate(pos.getX(), pos.getY(), pos.getZ());
		applet.sphere(m_fRadius);
		applet.popMatrix();
	}

}